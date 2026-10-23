<?php

use App\Actions\EnviarCertificadoAction;
use App\Console\Commands\EnviarCertificadoPendenteCommand;
use App\Jobs\EnviarLinkCertificadoJob;
use App\Mail\CertificadoNotification;
use App\Models\AgendaCursos;
use App\Models\Curso;
use App\Models\CursoInscrito;
use App\Models\Instrutor;
use Database\Factories\LancamentoFinanceiroFactory;
use Database\Factories\PessoaFactory;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    expect(DB::connection()->getDriverName())->toBe('sqlite');
    expect(DB::connection()->getDatabaseName())->toBe(':memory:');
    $this->certificadosCursoLog = tempnam(sys_get_temp_dir(), 'certificados-curso-');
    config([
        'logging.channels.certificados_falha.path' => $this->certificadosCursoLog,
        'logging.channels.mailsent' => config('logging.channels.null'),
    ]);
});

afterEach(function () {
    Log::forgetChannel('certificados_falha');
    unlink($this->certificadosCursoLog);
    $this->travelBack();
});

/** @param array<string, mixed> $attributes */
function inscritoCertificadoCurso(array $attributes = []): CursoInscrito
{
    $pessoa = PessoaFactory::new()->create();
    $curso = Curso::query()->create([
        'descricao' => 'Curso de certificado', 'tipo_curso' => 'OFICIAL',
        'carga_horaria' => 8, 'conteudo_programatico' => 'Conteúdo',
    ]);
    $instrutor = Instrutor::query()->create(['pessoa_id' => $pessoa->id, 'situacao' => true]);
    $agenda = AgendaCursos::query()->create([
        'curso_id' => $curso->id, 'instrutor_id' => $instrutor->id,
        'status' => 'REALIZADO', 'tipo_agendamento' => 'ONLINE',
        'inscricoes' => 1, 'investimento' => 100, 'investimento_associado' => 80,
        'data_inicio' => now()->subDay(), 'data_fim' => now()->subDay(),
    ]);
    $lancamento = LancamentoFinanceiroFactory::new()->create([
        'pessoa_id' => $pessoa->id, 'agenda_curso_id' => $agenda->id, 'status' => 'EFETIVADO',
    ]);

    return CursoInscrito::query()->create(array_merge([
        'agenda_curso_id' => $agenda->id, 'pessoa_id' => $pessoa->id,
        'nome' => 'Participante', 'email' => 'participante@example.com',
        'lancamento_financeiro_id' => $lancamento->id, 'valor' => 100, 'data_inscricao' => now(),
    ], $attributes));
}

function jobCertificadoCurso(): EnviarLinkCertificadoJob
{
    $payload = json_decode(DB::table('jobs')->orderByDesc('id')->value('payload'), true);

    return unserialize($payload['data']['command']);
}

it('recusa contato inválido no manual sem documento, marcação ou job', function (?string $email) {
    $inscrito = inscritoCertificadoCurso(['email' => $email, 'certificado_path' => 'anterior.pdf']);
    $original = $inscrito->fresh()->getAttributes();

    expect(fn () => app(EnviarCertificadoAction::class)->execute($inscrito))->toThrow(ValidationException::class);

    expect($inscrito->fresh()->getAttributes())->toBe($original);
    $this->assertDatabaseCount('dados_gera_doc', 0);
    $this->assertDatabaseCount('jobs', 0);
    expect(file_get_contents($this->certificadosCursoLog))->toContain('certificado_curso', 'Contato de certificado');
})->with(['', '   ', 'inválido', 'a@example.com, b@example.com']);

it('pula inválidos e enfileira somente o primeiro válido na ordem de inscrição e id', function () {
    $invalido = inscritoCertificadoCurso(['email' => 'inválido', 'data_inscricao' => now()->subDays(2)]);
    $valido = inscritoCertificadoCurso(['data_inscricao' => now()->subDay()]);
    $proximo = inscritoCertificadoCurso(['data_inscricao' => now()->subDay()]);

    $this->artisan('certificados:enviar-pendente')->assertSuccessful();

    expect(jobCertificadoCurso()->inscritoId)->toBe($valido->id);
    expect($invalido->fresh()->certificado_emitido)->toBeNull();
    expect($proximo->fresh()->certificado_emitido)->toBeNull();
    $this->assertDatabaseCount('dados_gera_doc', 1);
    $this->assertDatabaseCount('jobs', 1);
});

it('persiste destinatário normalizado e envia uma vez mesmo com passagem do tempo', function () {
    Mail::fake();
    $inscrito = inscritoCertificadoCurso(['email' => ' Participante@example.com ']);
    $doc = app(EnviarCertificadoAction::class)->execute($inscrito);
    $job = jobCertificadoCurso();
    expect($doc->content['participante_email'])->toBe('Participante@example.com');
    expect($job->to)->toBe('Participante@example.com');
    expect($inscrito->fresh()->certificado_envio_id)->toBe($job->tentativaId);
    expect($inscrito->fresh()->certificado_emitido)->not->toBeNull();
    $inscrito->update(['email' => 'outro@example.com']);
    $this->travel(2)->minutes();

    $job->handle();
    $job->handle();

    Mail::assertSent(CertificadoNotification::class, fn ($mail) => $mail->hasTo('Participante@example.com'));
    Mail::assertSentCount(1);
    expect($inscrito->fresh()->certificado_envio_id)->toBeNull();
    expect($inscrito->fresh()->certificado_emitido)->not->toBeNull();
});

it('defende o serviço de email contra destinatário inválido no job', function (string $email) {
    Mail::fake();
    $inscrito = inscritoCertificadoCurso();
    app(EnviarCertificadoAction::class)->execute($inscrito);
    $job = jobCertificadoCurso();
    $job->to = $email;

    expect(fn () => $job->handle())->toThrow(ValidationException::class);

    Mail::assertNothingSent();
    expect($inscrito->fresh()->certificado_envio_id)->toBe($job->tentativaId);
})->with(['', 'inválido']);

it('desfaz documento e marcação de curso se o enfileiramento falhar', function () {
    $inscrito = inscritoCertificadoCurso(['certificado_path' => 'anterior.pdf']);
    $original = $inscrito->fresh()->getAttributes();
    Bus::shouldReceive('dispatch')->once()->andThrow(new RuntimeException('Fila indisponível'));

    expect(fn () => app(EnviarCertificadoAction::class)->execute($inscrito))->toThrow(RuntimeException::class);

    expect($inscrito->fresh()->getAttributes())->toBe($original);
    $this->assertDatabaseCount('dados_gera_doc', 0);
    $this->assertDatabaseCount('jobs', 0);
});

it('libera somente a tentativa que falhou e rejeita jobs substituídos', function () {
    Mail::fake();
    $inscrito = inscritoCertificadoCurso();
    $action = app(EnviarCertificadoAction::class);
    $action->execute($inscrito);
    $antigo = jobCertificadoCurso();
    Cache::put(EnviarCertificadoPendenteCommand::SEM_CERTIFICADO_PENDENTE_CACHE_KEY, true, 600);

    $antigo->failed(new RuntimeException('SMTP indisponível'));

    expect($inscrito->fresh()->certificado_emitido)->toBeNull();
    expect($inscrito->fresh()->certificado_envio_id)->toBeNull();
    expect(Cache::has(EnviarCertificadoPendenteCommand::SEM_CERTIFICADO_PENDENTE_CACHE_KEY))->toBeFalse();
    expect(file_get_contents($this->certificadosCursoLog))->toContain('certificado_curso', 'SMTP indisponível', $antigo->tentativaId);
    $action->execute($inscrito);
    $atual = $inscrito->fresh()->getAttributes();

    $antigo->handle();
    $antigo->failed(new RuntimeException('Falha antiga'));

    expect($inscrito->fresh()->getAttributes())->toBe($atual);
    Mail::assertNothingSent();
});

it('revalida a pendência e preserva certificados históricos sem identificador', function () {
    $historico = inscritoCertificadoCurso(['certificado_emitido' => '2025-01-01 12:00:00']);
    $naoPago = inscritoCertificadoCurso();
    $naoPago->lancamentoFinanceiro->update(['status' => 'PROVISIONADO']);
    $pendente = inscritoCertificadoCurso();
    $action = app(EnviarCertificadoAction::class);

    expect($action->executePendente($historico->id))->toBeFalse();
    expect($action->executePendente($naoPago->id))->toBeFalse();
    expect($action->executePendente($pendente->id))->toBeTrue();
    expect($action->executePendente($pendente->id))->toBeFalse();

    expect($historico->fresh()->certificado_emitido)->toBe('2025-01-01 12:00:00');
    expect($historico->fresh()->certificado_envio_id)->toBeNull();
    $this->assertDatabaseCount('jobs', 1);
});

it('suspende consultas quando restam somente contatos inválidos sem criar jobs', function () {
    Mail::fake();
    $invalido = inscritoCertificadoCurso(['email' => 'inválido']);

    $this->artisan('certificados:enviar-pendente')->assertSuccessful();
    $this->artisan('certificados:enviar-pendente')->assertSuccessful();

    expect(Cache::has(EnviarCertificadoPendenteCommand::SEM_CERTIFICADO_PENDENTE_CACHE_KEY))->toBeTrue();
    expect($invalido->fresh()->certificado_emitido)->toBeNull();
    expect($invalido->fresh()->certificado_envio_id)->toBeNull();
    $this->assertDatabaseCount('jobs', 0);
    $this->assertDatabaseCount('dados_gera_doc', 0);
    Mail::assertNothingSent();
});

it('worker de cursos retenta três vezes e libera a pendência na falha definitiva', function () {
    $inscrito = inscritoCertificadoCurso();
    app(EnviarCertificadoAction::class)->execute($inscrito);
    $tentativa = jobCertificadoCurso()->tentativaId;
    Mail::shouldReceive('to')->with('participante@example.com')->times(3)->andThrow(new RuntimeException('SMTP de teste indisponível'));
    Mail::shouldReceive('to')->with('sistema@redemetrologica.com.br')->andReturnSelf();
    Mail::shouldReceive('send')->andReturnNull();
    config(['logging.channels.single' => config('logging.channels.null')]);
    $this->freezeTime();

    for ($numero = 1; $numero <= 3; $numero++) {
        $this->artisan('queue:work', ['connection' => 'database', '--once' => true, '--sleep' => 0])->assertSuccessful();

        if ($numero < 3) {
            expect($inscrito->fresh()->certificado_envio_id)->toBe($tentativa);
            $this->assertDatabaseCount('failed_jobs', 0);
            expect(DB::table('jobs')->value('available_at'))->toBe(now()->addSeconds(60)->timestamp);
            $this->travel(60)->seconds();
        }
    }

    $this->assertDatabaseCount('failed_jobs', 1);
    $this->assertDatabaseCount('jobs', 0);
    expect($inscrito->fresh()->certificado_emitido)->toBeNull();
    expect($inscrito->fresh()->certificado_envio_id)->toBeNull();
    expect(file_get_contents($this->certificadosCursoLog))->toContain('certificado_curso', 'Falha definitiva', $tentativa);
});

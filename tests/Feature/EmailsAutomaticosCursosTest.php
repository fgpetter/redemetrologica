<?php

use App\Actions\EnfileirarConferenciaCursoAction;
use App\Actions\EnfileirarEmailInscritoCursoAction;
use App\Jobs\EnviarConferenciaCursoJob;
use App\Jobs\EnviarInformacoesCursoJob;
use App\Jobs\EnviarPendenciasCursoJob;
use App\Mail\AvisoConferenciaTreinamentoMail;
use App\Mail\AvisoPendenciasTreinamentoMail;
use App\Mail\InformacoesTreinamentoMail;
use App\Models\AgendaCursos;
use App\Models\Curso;
use App\Models\CursoInscrito;
use App\Models\Instrutor;
use App\Support\ProcessosAgendados;
use Carbon\Carbon;
use Database\Factories\PessoaFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->validationLog = tempnam(sys_get_temp_dir(), 'validacao-cursos-');
    config([
        'logging.channels.validation.path' => $this->validationLog,
        'logging.channels.mailsent' => config('logging.channels.null'),
    ]);
    Log::forgetChannel('validation');
});

afterEach(function () {
    Log::forgetChannel('validation');
    unlink($this->validationLog);
});

function agendaEmail(string $data, array $extra = []): AgendaCursos
{
    $curso = Curso::create(['descricao' => 'Curso exemplo', 'tipo_curso' => 'OFICIAL']);
    $instrutor = Instrutor::create(['pessoa_id' => PessoaFactory::new()->create()->id, 'situacao' => true]);

    return AgendaCursos::create(array_merge([
        'curso_id' => $curso->id,
        'instrutor_id' => $instrutor->id,
        'status' => 'CONFIRMADO',
        'tipo_agendamento' => 'ONLINE',
        'data_inicio' => $data,
        'horario' => '08:00',
        'link_aula' => 'https://example.com/aula',
    ], $extra));
}

function materialEmail(AgendaCursos $agenda, string $descricao = 'Apostila'): void
{
    $agenda->materiaisTreinamento()->create(['arquivo' => 'cursos/material.pdf', 'descricao' => $descricao]);
}

function criarInscritoEmail(AgendaCursos $agenda, array $extra = []): CursoInscrito
{
    return $agenda->inscritos()->create(array_merge([
        'pessoa_id' => PessoaFactory::new()->create()->id,
        'nome' => 'Ana',
        'email' => 'ana@example.com',
        'data_inscricao' => now('America/Sao_Paulo'),
    ], $extra));
}

/**
 * @template T of object
 *
 * @param  class-string<T>  $classe
 * @return T
 */
function jobCurso(string $classe): object
{
    $job = DB::table('jobs')->orderByDesc('id')->pluck('payload')
        ->map(fn (string $payload) => unserialize(json_decode($payload, true)['data']['command']))
        ->first(fn (object $job) => $job instanceof $classe);

    expect($job)->toBeInstanceOf($classe);

    return $job;
}

it('enfileira todos os envios na ordem conferência, pendências e participantes', function () {
    Mail::fake();
    $this->travelTo(Carbon::parse('2026-11-14 06:00:00', 'America/Sao_Paulo'));

    $conferencia = agendaEmail('2026-11-16');
    $pendente = agendaEmail('2026-11-15', ['link_aula' => null]);
    $participantes = agendaEmail('2026-11-15');
    materialEmail($participantes);
    $antigo = criarInscritoEmail($participantes, ['nome' => 'Antigo', 'email' => 'antigo@example.com', 'data_inscricao' => now()->subDays(2)]);
    $recente = criarInscritoEmail($participantes, ['nome' => 'Recente', 'email' => 'recente@example.com', 'data_inscricao' => now()->subDay()]);

    $this->artisan('cursos:emails-automaticos')->assertSuccessful();
    $jobs = DB::table('jobs')->orderBy('id')->pluck('payload')
        ->map(fn (string $payload) => unserialize(json_decode($payload, true)['data']['command']));

    expect($jobs->map(fn (object $job) => $job::class)->all())->toBe([
        EnviarConferenciaCursoJob::class,
        EnviarPendenciasCursoJob::class,
        EnviarInformacoesCursoJob::class,
        EnviarInformacoesCursoJob::class,
    ]);
    expect($jobs[0]->agendaCursoId)->toBe($conferencia->id)
        ->and($jobs[1]->agendaCursoId)->toBe($pendente->id)
        ->and($jobs[2]->inscritoId)->toBe($antigo->id)
        ->and($jobs[3]->inscritoId)->toBe($recente->id);

    $this->artisan('cursos:emails-automaticos')->assertSuccessful();
    $this->assertDatabaseCount('jobs', 4);
});

it('volta a consultar quando um candidato é criado depois de uma execução vazia', function () {
    Mail::fake();
    $this->travelTo(Carbon::parse('2026-11-14 06:00:00', 'America/Sao_Paulo'));

    $this->artisan('cursos:emails-automaticos')->assertSuccessful();
    $this->assertDatabaseCount('jobs', 0);

    $novo = agendaEmail('2026-11-16');
    $this->artisan('cursos:emails-automaticos')->assertSuccessful();

    expect(jobCurso(EnviarConferenciaCursoJob::class)->agendaCursoId)->toBe($novo->id);
});

it('usa a data da rodada e a fila agendada mesmo após a virada do dia', function () {
    Mail::fake();
    $this->travelTo(Carbon::parse('2026-11-15 00:01:00', 'America/Sao_Paulo'));
    $conferencia = agendaEmail('2026-11-16');
    $pendente = agendaEmail('2026-11-15', ['link_aula' => null]);
    agendaEmail('2026-11-17');

    ProcessosAgendados::executar(['rodada_id' => 'teste', 'data_rodada' => '2026-11-14'], function (): void {
        $this->artisan('cursos:emails-automaticos')->assertSuccessful()->run();
    });

    expect(jobCurso(EnviarConferenciaCursoJob::class)->agendaCursoId)->toBe($conferencia->id);
    expect(jobCurso(EnviarPendenciasCursoJob::class)->agendaCursoId)->toBe($pendente->id);
    $this->assertDatabaseCount('jobs', 2);
    expect(DB::table('jobs')->distinct()->pluck('queue')->all())->toBe([ProcessosAgendados::FILA_EMAIL]);
});

it('não enfileira novamente candidatos com reserva vigente', function () {
    Mail::fake();
    $this->travelTo(Carbon::parse('2026-11-14 06:00:00', 'America/Sao_Paulo'));
    $agenda = agendaEmail('2026-11-16');

    app(EnfileirarConferenciaCursoAction::class)->execute($agenda->id, '2026-11-16');

    expect(app(EnfileirarConferenciaCursoAction::class)->execute($agenda->id, '2026-11-16'))->toBeFalse();
    $this->assertDatabaseCount('jobs', 1);
    expect($agenda->fresh()->conferencia_envio_id)->not->toBeNull();
});

it('libera a reserva quando a agenda deixa de ser elegível antes do envio', function () {
    Mail::fake();
    $this->travelTo(Carbon::parse('2026-11-14 06:00:00', 'America/Sao_Paulo'));
    $agenda = agendaEmail('2026-11-16');
    app(EnfileirarConferenciaCursoAction::class)->execute($agenda->id, '2026-11-16');
    $job = jobCurso(EnviarConferenciaCursoJob::class);

    $agenda->update(['status' => 'CANCELADO']);
    $job->handle();

    Mail::assertNothingSent();
    expect($agenda->fresh()->conferencia_envio_id)->toBeNull()
        ->and($agenda->fresh()->conferencia_enviada_em)->toBeNull();
});

it('processa os três tipos de email mesmo após o encerramento da janela de enfileiramento', function (string $tipo) {
    Mail::fake();
    $this->travelTo(Carbon::parse('2026-11-14 06:00:00', 'America/Sao_Paulo'));
    $conferencia = agendaEmail('2026-11-16', ['tipo_agendamento' => $tipo]);
    $pendencias = agendaEmail('2026-11-15', ['link_aula' => null, 'tipo_agendamento' => $tipo]);
    $participantes = agendaEmail('2026-11-15', ['tipo_agendamento' => $tipo]);
    materialEmail($participantes);
    $inscrito = criarInscritoEmail($participantes);

    $this->artisan('cursos:emails-automaticos')->assertSuccessful();
    $jobConferencia = jobCurso(EnviarConferenciaCursoJob::class);
    $jobPendencias = jobCurso(EnviarPendenciasCursoJob::class);
    $jobParticipante = jobCurso(EnviarInformacoesCursoJob::class);

    $this->travelTo(Carbon::parse('2026-11-17 06:00:00', 'America/Sao_Paulo'));
    $jobConferencia->handle();
    $jobPendencias->handle();
    $jobParticipante->handle();
    $jobConferencia->handle();
    $jobPendencias->handle();
    $jobParticipante->handle();

    Mail::assertSentCount(3);
    expect($conferencia->fresh()->conferencia_enviada_em)->not->toBeNull()
        ->and($pendencias->fresh()->pendencias_enviadas_em)->not->toBeNull()
        ->and($inscrito->fresh()->informacoes_enviadas_em)->not->toBeNull();
})->with(['ONLINE', 'IN-COMPANY']);

it('seleciona a conferência de dois dias depois já à meia-noite', function () {
    $this->travelTo(Carbon::parse('2026-11-14 00:00:00', 'America/Sao_Paulo'));
    $agenda = agendaEmail('2026-11-16');

    $this->artisan('cursos:emails-automaticos')->assertSuccessful();
    expect(jobCurso(EnviarConferenciaCursoJob::class)->agendaCursoId)->toBe($agenda->id);
    $this->assertDatabaseCount('jobs', 1);
    expect(DB::table('jobs')->value('queue'))->toBe('default');
});

it('ignora e registra no canal validation o e-mail inválido sem impedir o próximo inscrito', function (string $email) {
    Mail::fake();
    $this->travelTo(Carbon::parse('2026-11-14 06:00:00', 'America/Sao_Paulo'));
    $agenda = agendaEmail('2026-11-15');
    materialEmail($agenda);
    $invalido = criarInscritoEmail($agenda, ['email' => $email, 'data_inscricao' => now()->subDays(2)]);
    $valido = criarInscritoEmail($agenda, ['email' => 'valido@example.com', 'data_inscricao' => now()->subDay()]);

    $this->artisan('cursos:emails-automaticos')->assertSuccessful();

    expect(jobCurso(EnviarInformacoesCursoJob::class)->inscritoId)->toBe($valido->id)
        ->and($invalido->fresh()->informacoes_envio_id)->toBeNull()
        ->and($invalido->fresh()->informacoes_enviadas_em)->toBeNull();
    expect(file_get_contents($this->validationLog))->toContain('E-mail inválido ou ausente');
})->with(['invalido', '']);

it('congela destinatário, nome, treinamento, data, horário, link e materiais no enfileiramento', function () {
    Mail::fake();
    $this->travelTo(Carbon::parse('2026-11-14 06:00:00', 'America/Sao_Paulo'));
    $agenda = agendaEmail('2026-11-15');
    materialEmail($agenda);
    $inscrito = criarInscritoEmail($agenda);

    $this->artisan('cursos:emails-automaticos')->assertSuccessful();
    $job = jobCurso(EnviarInformacoesCursoJob::class);

    $inscrito->update(['nome' => 'Novo nome', 'email' => 'novo@example.com']);
    $agenda->update(['link_aula' => 'https://example.com/novo', 'horario' => '09:00']);
    $agenda->materiaisTreinamento()->first()->update(['descricao' => 'Novo material']);

    $job->handle();

    Mail::assertSent(InformacoesTreinamentoMail::class, function (InformacoesTreinamentoMail $mail): bool {
        return $mail->hasTo('ana@example.com')
            && $mail->dados['nome'] === 'ANA'
            && $mail->dados['treinamento'] === 'Curso exemplo'
            && $mail->dados['data_curso'] === '2026-11-15'
            && $mail->dados['horario'] === '08:00'
            && $mail->dados['link'] === 'https://example.com/aula'
            && $mail->dados['materiais'][0]['descricao'] === 'Apostila';
    });
});

it('confirma a agenda somente após a confirmação de todos os inscritos', function () {
    Mail::fake();
    $this->travelTo(Carbon::parse('2026-11-14 06:00:00', 'America/Sao_Paulo'));
    $agenda = agendaEmail('2026-11-15');
    materialEmail($agenda);
    $primeiro = criarInscritoEmail($agenda, ['data_inscricao' => now()->subDays(2)]);
    criarInscritoEmail($agenda, ['email' => 'segundo@example.com']);

    $this->artisan('cursos:emails-automaticos')->assertSuccessful();
    $this->travelTo(Carbon::parse('2026-11-14 06:01:00', 'America/Sao_Paulo'));
    $this->artisan('cursos:emails-automaticos')->assertSuccessful();

    $primeiroJob = json_decode(DB::table('jobs')->orderBy('id')->value('payload'), true);
    unserialize($primeiroJob['data']['command'])->handle();

    expect($agenda->fresh()->informacoes_enviadas_em)->toBeNull()
        ->and($primeiro->fresh()->informacoes_enviadas_em)->not->toBeNull();

    $segundoJob = json_decode(DB::table('jobs')->orderByDesc('id')->value('payload'), true);
    unserialize($segundoJob['data']['command'])->handle();

    expect($agenda->fresh()->informacoes_enviadas_em)->not->toBeNull();
});

it('não preenche a confirmação geral de agenda sem inscritos', function () {
    Mail::fake();
    $this->travelTo(Carbon::parse('2026-11-14 06:00:00', 'America/Sao_Paulo'));
    $agenda = agendaEmail('2026-11-15');
    materialEmail($agenda);

    $this->artisan('cursos:emails-automaticos')->assertSuccessful();

    $this->assertDatabaseCount('jobs', 0);
    expect($agenda->fresh()->informacoes_enviadas_em)->toBeNull();
});

it('libera a reserva após falha definitiva vigente e permite novo enfileiramento', function () {
    Mail::fake();
    $this->travelTo(Carbon::parse('2026-11-14 06:00:00', 'America/Sao_Paulo'));
    $agenda = agendaEmail('2026-11-16');
    app(EnfileirarConferenciaCursoAction::class)->execute($agenda->id, '2026-11-16');
    $job = jobCurso(EnviarConferenciaCursoJob::class);
    $job->failed(new RuntimeException('SMTP indisponível'));

    expect($agenda->fresh()->conferencia_envio_id)->toBeNull();

    $this->artisan('cursos:emails-automaticos')->assertSuccessful();
    expect(jobCurso(EnviarConferenciaCursoJob::class)->tentativaId)->not->toBe($job->tentativaId);
});

it('não permite que uma tentativa antiga altere reservas posteriores', function () {
    Mail::fake();
    $this->travelTo(Carbon::parse('2026-11-14 06:00:00', 'America/Sao_Paulo'));
    $agenda = agendaEmail('2026-11-16');
    app(EnfileirarConferenciaCursoAction::class)->execute($agenda->id, '2026-11-16');
    $antiga = jobCurso(EnviarConferenciaCursoJob::class);
    $antiga->failed(new RuntimeException('Falha antiga'));

    app(EnfileirarConferenciaCursoAction::class)->execute($agenda->id, '2026-11-16');
    $nova = jobCurso(EnviarConferenciaCursoJob::class);

    $antiga->handle();
    $antiga->failed(new RuntimeException('Falha antiga'));

    Mail::assertNothingSent();
    expect($agenda->fresh()->conferencia_envio_id)->toBe($nova->tentativaId);
});

it('desfaz a reserva quando a inserção na fila falha', function () {
    $this->travelTo(Carbon::parse('2026-11-14 06:00:00', 'America/Sao_Paulo'));
    $agenda = agendaEmail('2026-11-16');
    config(['queue.connections.database.table' => 'fila_inexistente']);

    $this->artisan('cursos:emails-automaticos')->assertFailed();

    expect($agenda->fresh()->conferencia_envio_id)->toBeNull();
    $this->assertDatabaseCount('jobs', 0);
});

it('isola a falha de uma agenda e continua até enfileirar outra', function () {
    Mail::fake();
    $this->travelTo(Carbon::parse('2026-11-14 06:00:00', 'America/Sao_Paulo'));
    $primeira = agendaEmail('2026-11-16');
    $segunda = agendaEmail('2026-11-16');

    $this->app->instance(EnfileirarConferenciaCursoAction::class, new class($primeira->id) extends EnfileirarConferenciaCursoAction
    {
        public function __construct(private int $falhaId) {}

        public function execute(int $agendaCursoId, string $dataEsperada, bool $agendado = false): bool
        {
            if ($agendaCursoId === $this->falhaId) {
                throw new RuntimeException('Falha simulada');
            }

            return parent::execute($agendaCursoId, $dataEsperada, $agendado);
        }
    });

    $this->artisan('cursos:emails-automaticos')->assertFailed();

    expect(jobCurso(EnviarConferenciaCursoJob::class)->agendaCursoId)->toBe($segunda->id);
    $this->assertDatabaseCount('jobs', 1);
});

it('exige envio manual aos participantes após o aviso de pendências', function () {
    Mail::fake();
    $this->travelTo(Carbon::parse('2026-11-14 06:00:00', 'America/Sao_Paulo'));
    $agenda = agendaEmail('2026-11-15', ['link_aula' => null]);
    $inscrito = criarInscritoEmail($agenda);

    $this->artisan('cursos:emails-automaticos')->assertSuccessful();
    jobCurso(EnviarPendenciasCursoJob::class)->handle();
    expect($agenda->fresh()->pendencias_enviadas_em)->not->toBeNull();

    $agenda->update(['link_aula' => 'https://example.com/aula']);
    materialEmail($agenda);
    $this->travelTo(Carbon::parse('2026-11-14 06:01:00', 'America/Sao_Paulo'));
    $this->artisan('cursos:emails-automaticos')->assertSuccessful();

    Mail::assertNotSent(InformacoesTreinamentoMail::class);
    expect($inscrito->fresh()->informacoes_envio_id)->toBeNull()
        ->and($inscrito->fresh()->informacoes_enviadas_em)->toBeNull();
});

it('não envia aviso de pendências depois que todos os inscritos receberam', function () {
    Mail::fake();
    $this->travelTo(Carbon::parse('2026-11-14 06:00:00', 'America/Sao_Paulo'));
    $agenda = agendaEmail('2026-11-15', ['link_aula' => null, 'informacoes_enviadas_em' => now()]);
    criarInscritoEmail($agenda, ['informacoes_enviadas_em' => now()]);

    $this->artisan('cursos:emails-automaticos')->assertSuccessful();

    Mail::assertNotSent(AvisoPendenciasTreinamentoMail::class);
    $this->assertDatabaseCount('jobs', 0);
});

it('concilia a confirmação geral na seleção mesmo quando faltam recursos', function () {
    Mail::fake();
    $this->travelTo(Carbon::parse('2026-11-14 06:00:00', 'America/Sao_Paulo'));
    $agenda = agendaEmail('2026-11-15', ['link_aula' => null]);
    criarInscritoEmail($agenda, ['informacoes_enviadas_em' => now()]);

    $this->artisan('cursos:emails-automaticos')->assertSuccessful();

    expect($agenda->fresh()->informacoes_enviadas_em)->not->toBeNull();
    $this->assertDatabaseCount('jobs', 0);
    Mail::assertNothingSent();
});

it('não pula agendas após conciliar um lote de confirmações', function () {
    $this->travelTo(Carbon::parse('2026-11-14 06:00:00', 'America/Sao_Paulo'));
    $primeira = agendaEmail('2026-11-15', ['link_aula' => null]);
    $inscrito = criarInscritoEmail($primeira, ['informacoes_enviadas_em' => now()]);

    for ($i = 1; $i < 100; $i++) {
        $agenda = $primeira->replicate(['uid']);
        $agenda->save();
        $copia = $inscrito->replicate(['uid']);
        $copia->agenda_curso_id = $agenda->id;
        $copia->save();
    }

    $pendente = $primeira->replicate(['uid']);
    $pendente->save();

    $this->artisan('cursos:emails-automaticos')->assertSuccessful();

    expect(jobCurso(EnviarPendenciasCursoJob::class)->agendaCursoId)->toBe($pendente->id);
    $this->assertDatabaseCount('jobs', 1);
});

it('percorre inscritos em ordem mesmo quando confirmações mudam durante a consulta', function () {
    $this->travelTo(Carbon::parse('2026-11-14 06:00:00', 'America/Sao_Paulo'));
    $agenda = agendaEmail('2026-11-15');
    materialEmail($agenda);
    $primeiro = criarInscritoEmail($agenda, ['informacoes_envio_id' => 'reservado']);
    for ($i = 1; $i < 100; $i++) {
        $copia = $primeiro->replicate(['uid']);
        $copia->save();
    }
    $pendente = $primeiro->replicate(['uid']);
    $pendente->informacoes_envio_id = null;
    $pendente->save();

    $this->app->instance(EnfileirarEmailInscritoCursoAction::class, new class($primeiro->id) extends EnfileirarEmailInscritoCursoAction
    {
        private bool $alterado = false;

        public function __construct(private int $primeiroId) {}

        public function execute(int $agendaCursoId, int $inscritoId, string $dataEsperada, bool $agendado = false): bool
        {
            if (! $this->alterado) {
                CursoInscrito::query()->whereKey($this->primeiroId)->update(['informacoes_enviadas_em' => now()]);
                $this->alterado = true;
            }

            return parent::execute($agendaCursoId, $inscritoId, $dataEsperada, $agendado);
        }
    });

    $this->artisan('cursos:emails-automaticos')->assertSuccessful();
    expect(jobCurso(EnviarInformacoesCursoJob::class)->inscritoId)->toBe($pendente->id);
    $this->assertDatabaseCount('jobs', 1);
});

it('renderiza os conteúdos dos emails a partir do snapshot congelado', function () {
    Mail::fake();
    $this->travelTo(Carbon::parse('2026-11-14 06:00:00', 'America/Sao_Paulo'));
    agendaEmail('2026-11-16');
    agendaEmail('2026-11-15', ['link_aula' => null]);
    $participantes = agendaEmail('2026-11-15');
    materialEmail($participantes);
    criarInscritoEmail($participantes);

    $this->artisan('cursos:emails-automaticos')->assertSuccessful();
    $conferencia = new AvisoConferenciaTreinamentoMail(jobCurso(EnviarConferenciaCursoJob::class)->snapshot);
    $conferencia->assertHasSubject('Conferência de link e materiais')
        ->assertSeeInHtml('15/11/2026, serão enviados os materiais e link do zoom para o treinamento Curso exemplo.');

    $aviso = new AvisoPendenciasTreinamentoMail(jobCurso(EnviarPendenciasCursoJob::class)->snapshot);
    $aviso->assertSeeInHtml('15/11/2026')
        ->assertSeeInHtml('O e-mail de materiais e link de acesso não foram enviados aos inscritos. As informações estão faltantes ou inválidas.')
        ->assertSeeInHtml('Revise os dados e')
        ->assertSeeInHtml('envie manualmente')
        ->assertSeeInHtml('para cada inscrito.');

    $informacoes = new InformacoesTreinamentoMail(jobCurso(EnviarInformacoesCursoJob::class)->snapshot);
    $informacoes->assertHasSubject('Material e informações de acesso - Curso exemplo')
        ->assertSeeInHtml('ANA')
        ->assertSeeInHtml('15/11/2026')
        ->assertSeeInHtml('08:00')
        ->assertSeeInHtml('https://example.com/aula')
        ->assertSeeInHtml('Apostila')
        ->assertSeeInHtml('/storage/cursos/material.pdf')
        ->assertSeeInHtml('Favor confirmar recebimento');
});

it('registra activity log e timestamps ao reservar e confirmar o envio', function () {
    Mail::fake();
    $this->travelTo(Carbon::parse('2026-11-14 06:00:00', 'America/Sao_Paulo'));
    $agenda = agendaEmail('2026-11-16');
    $logs = DB::table('activity_log')->count();

    $this->artisan('cursos:emails-automaticos')->assertSuccessful();
    expect(DB::table('activity_log')->count())->toBeGreaterThan($logs);

    $this->travelTo(Carbon::parse('2026-11-14 06:01:00', 'America/Sao_Paulo'));
    jobCurso(EnviarConferenciaCursoJob::class)->handle();

    expect($agenda->fresh()->conferencia_enviada_em)->not->toBeNull()
        ->and($agenda->fresh()->updated_at->equalTo(now()))->toBeTrue()
        ->and(DB::table('activity_log')->count())->toBeGreaterThan($logs + 1);
});

it('worker retenta três vezes e registra falha definitiva liberando a reserva', function () {
    $this->travelTo(Carbon::parse('2026-11-14 06:00:00', 'America/Sao_Paulo'));
    $agenda = agendaEmail('2026-11-16');
    app(EnfileirarConferenciaCursoAction::class)->execute($agenda->id, '2026-11-16');
    $job = jobCurso(EnviarConferenciaCursoJob::class);
    Mail::shouldReceive('to')->with('administrativo@redemetrologica.com.br')
        ->times(3)
        ->andThrow(new RuntimeException('SMTP de teste indisponível'));
    config(['logging.channels.single' => config('logging.channels.null')]);

    for ($tentativa = 1; $tentativa <= 3; $tentativa++) {
        $this->artisan('queue:work', ['connection' => 'database', '--once' => true, '--sleep' => 0])->assertSuccessful();

        if ($tentativa < 3) {
            expect($agenda->fresh()->conferencia_envio_id)->toBe($job->tentativaId);
            $this->assertDatabaseCount('failed_jobs', 0);
            expect(DB::table('jobs')->value('available_at'))->toBe(now()->addSeconds(60)->timestamp);
            $this->travel(60)->seconds();
        }
    }

    $this->assertDatabaseCount('failed_jobs', 1);
    $this->assertDatabaseCount('jobs', 0);
    expect($agenda->fresh()->conferencia_envio_id)->toBeNull()
        ->and($agenda->fresh()->conferencia_enviada_em)->toBeNull();
});

it('processa o job pelo worker com transporte array após a abertura da janela', function () {
    config(['mail.default' => 'array']);
    $this->travelTo(Carbon::parse('2026-11-14 06:00:00', 'America/Sao_Paulo'));
    $agenda = agendaEmail('2026-11-16');
    $this->artisan('cursos:emails-automaticos')->assertSuccessful();

    expect(DB::table('jobs')->count())->toBe(1);

    $this->artisan('queue:work', ['connection' => 'database', '--once' => true])->assertSuccessful();

    expect($agenda->fresh()->conferencia_enviada_em)->not->toBeNull()
        ->and($agenda->fresh()->conferencia_envio_id)->toBeNull()
        ->and(DB::table('jobs')->count())->toBe(0);
});

it('não reenvia quando o mesmo job é processado novamente', function () {
    Mail::fake();
    $this->travelTo(Carbon::parse('2026-11-14 06:00:00', 'America/Sao_Paulo'));
    $agenda = agendaEmail('2026-11-16');
    $this->artisan('cursos:emails-automaticos')->assertSuccessful();
    $job = jobCurso(EnviarConferenciaCursoJob::class);

    $job->handle();
    $job->handle();

    Mail::assertSentCount(1);
    expect($agenda->fresh()->conferencia_enviada_em)->not->toBeNull();
});

<?php

use App\Actions\EnfileirarCertificadoInterlabPendenteAction;
use App\Actions\EnviarCertificadoInterlabAction;
use App\Console\Commands\EnviarCertificadoInterlabPendenteCommand;
use App\Jobs\EnviarCertificadoInterlabPendenteJob;
use App\Jobs\GerarCertificadoInterlabJob;
use App\Mail\CertificadoInterlabMail;
use App\Models\InterlabInscrito;
use Database\Factories\AgendaInterlabFactory;
use Database\Factories\InterlabInscritoFactory;
use Database\Factories\LancamentoFinanceiroFactory;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    expect(DB::connection()->getDriverName())->toBe('sqlite');
    expect(DB::connection()->getDatabaseName())->toBe(':memory:');
    $this->travelTo(new DateTimeImmutable('2026-10-23 00:00:00-03:00'));
    $this->certificadosLog = tempnam(sys_get_temp_dir(), 'certificados-falha-');
    config([
        'logging.channels.certificados_falha.path' => $this->certificadosLog,
        'logging.channels.mailsent' => config('logging.channels.null'),
    ]);
});

afterEach(function () {
    Log::forgetChannel('certificados_falha');
    unlink($this->certificadosLog);
    $this->travelBack();
});

/** @param array<string, mixed> $attributes */
function inscritoCertificadoInterlab(array $attributes = [], string $statusAgenda = 'CONCLUIDO', ?string $statusPagamento = 'EFETIVADO'): InterlabInscrito
{
    $agenda = AgendaInterlabFactory::new()->create(['status' => $statusAgenda]);
    $inscrito = InterlabInscritoFactory::new()->create(array_merge([
        'agenda_interlab_id' => $agenda->id,
        'email' => 'tecnico@example.com',
    ], $attributes));
    $inscrito->pessoa->update(['email' => 'inscricao@example.com']);

    if ($statusPagamento !== null) {
        $lancamento = LancamentoFinanceiroFactory::new()->create([
            'pessoa_id' => $inscrito->empresa_id,
            'agenda_interlab_id' => $agenda->id,
            'status' => $statusPagamento,
        ]);
        $inscrito->update(['lancamento_financeiro_id' => $lancamento->id]);
    }

    return $inscrito;
}

function jobCertificadoInterlab(): EnviarCertificadoInterlabPendenteJob
{
    $payload = json_decode(DB::table('jobs')->orderByDesc('id')->value('payload'), true);

    return unserialize($payload['data']['command']);
}

it('enfileira apenas o mais antigo e desempata por id, sem reenviar inscrições marcadas', function () {
    $primeiro = inscritoCertificadoInterlab(['data_inscricao' => now()->subDays(2)]);
    $segundo = inscritoCertificadoInterlab(['data_inscricao' => now()->subDays(2)]);
    inscritoCertificadoInterlab(['data_inscricao' => now()->subDay()]);

    $this->artisan('interlab:enviar-certificado-pendente')->assertSuccessful();

    $job = jobCertificadoInterlab();
    expect($job->inscritoId)->toBe($primeiro->id);
    expect($job->to)->toBe('tecnico@example.com');
    expect($job->cc)->toBe('inscricao@example.com');
    expect($primeiro->fresh()->certificado_emitido)->not->toBeNull();
    expect($primeiro->fresh()->certificado_envio_id)->toBe($job->tentativaId);
    $this->assertDatabaseCount('jobs', 1);
    $this->assertDatabaseCount('dados_gera_doc', 1);

    $this->artisan('interlab:enviar-certificado-pendente')->assertSuccessful();

    expect(jobCertificadoInterlab()->inscritoId)->toBe($segundo->id);
    $this->assertDatabaseCount('jobs', 2);
});

it('ignora inscrições inelegíveis', function (string $agenda, ?string $pagamento, ?string $emitido) {
    Queue::fake();
    Mail::fake();
    inscritoCertificadoInterlab(['certificado_emitido' => $emitido], $agenda, $pagamento);

    $this->artisan('interlab:enviar-certificado-pendente')->assertSuccessful();

    Queue::assertNothingPushed();
    $this->assertDatabaseCount('dados_gera_doc', 0);
})->with([
    ['AGENDADO', 'EFETIVADO', null],
    ['CONFIRMADO', 'EFETIVADO', null],
    ['CONCLUIDO', 'PROVISIONADO', null],
    ['CONCLUIDO', null, null],
    ['CONCLUIDO', 'EFETIVADO', '2026-01-01 12:00:00'],
]);

it('normaliza, deduplica e usa o endereço válido em Para', function (?string $tecnico, ?string $pessoa, string $to, ?string $cc) {
    $inscrito = inscritoCertificadoInterlab(['email' => $tecnico]);
    $inscrito->pessoa->update(['email' => $pessoa]);

    $this->artisan('interlab:enviar-certificado-pendente')->assertSuccessful();

    $job = jobCertificadoInterlab();
    expect($job->to)->toBe($to);
    expect($job->cc)->toBe($cc);
})->with([
    [' tecnico@example.com ', ' inscricao@example.com ', 'tecnico@example.com', 'inscricao@example.com'],
    ['TECNICO@example.com', 'tecnico@EXAMPLE.com', 'TECNICO@example.com', null],
    ['igual@example.com', 'igual@example.com', 'igual@example.com', null],
    [null, 'inscricao@example.com', 'inscricao@example.com', null],
    ['', 'inscricao@example.com', 'inscricao@example.com', null],
    ['inválido', 'inscricao@example.com', 'inscricao@example.com', null],
    ['tecnico@example.com', null, 'tecnico@example.com', null],
    ['tecnico@example.com', 'inválido', 'tecnico@example.com', null],
]);

it('pula contatos inválidos sem documento ou marcação e continua na próxima inscrição', function (?string $email) {
    $invalido = inscritoCertificadoInterlab(['email' => $email, 'data_inscricao' => now()->subDays(3)]);
    $invalido->pessoa->update(['email' => $email]);
    $valido = inscritoCertificadoInterlab();

    $this->artisan('interlab:enviar-certificado-pendente')->assertSuccessful();

    expect($invalido->fresh()->certificado_emitido)->toBeNull();
    expect(jobCertificadoInterlab()->inscritoId)->toBe($valido->id);
    $this->assertDatabaseCount('dados_gera_doc', 1);
    expect(file_get_contents($this->certificadosLog))->toContain('Contato de certificado ausente ou inválido.');
})->with([null, '', '   ', 'sem-arroba', 'nome@example.com, outro@example.com']);

it('revalida elegibilidade antes de enfileirar', function () {
    $inscrito = inscritoCertificadoInterlab();
    $inscrito->lancamentoFinanceiro->update(['status' => 'PROVISIONADO']);

    expect(app(EnfileirarCertificadoInterlabPendenteAction::class)->execute($inscrito->id))->toBeFalse();
    $this->assertDatabaseCount('jobs', 0);
    $this->assertDatabaseCount('dados_gera_doc', 0);
});

it('desfaz documento e marcação se o enfileiramento falhar', function () {
    $inscrito = inscritoCertificadoInterlab(['certificado_path' => 'arquivo-anterior.pdf']);
    Bus::shouldReceive('dispatch')->once()->andThrow(new RuntimeException('Fila indisponível'));

    expect(fn () => app(EnfileirarCertificadoInterlabPendenteAction::class)->execute($inscrito->id))
        ->toThrow(RuntimeException::class, 'Fila indisponível');

    expect($inscrito->fresh()->certificado_emitido)->toBeNull();
    expect($inscrito->fresh()->certificado_envio_id)->toBeNull();
    expect($inscrito->fresh()->certificado_path)->toBe('arquivo-anterior.pdf');
    $this->assertDatabaseCount('dados_gera_doc', 0);
    $this->assertDatabaseCount('jobs', 0);
});

it('envia uma única mensagem com Para e Cc e ignora repetição do mesmo job', function () {
    Mail::fake();
    $inscrito = inscritoCertificadoInterlab();
    app(EnfileirarCertificadoInterlabPendenteAction::class)->execute($inscrito->id);
    $job = jobCertificadoInterlab();
    $inscrito->pessoa->update(['email' => 'alterado@example.com']);

    $job->handle();
    $job->handle();

    Mail::assertSent(CertificadoInterlabMail::class, fn ($mail) => $mail->hasTo('tecnico@example.com') && $mail->hasCc('inscricao@example.com'));
    Mail::assertSentCount(1);
    expect($inscrito->fresh()->certificado_envio_id)->toBeNull();
    expect($inscrito->fresh()->certificado_emitido)->not->toBeNull();
});

it('recusa payload inválido antes de chamar o serviço de email', function (string $to, ?string $cc) {
    Mail::fake();
    $inscrito = inscritoCertificadoInterlab();
    app(EnfileirarCertificadoInterlabPendenteAction::class)->execute($inscrito->id);
    $job = jobCertificadoInterlab();
    $job->to = $to;
    $job->cc = $cc;

    expect(fn () => $job->handle())->toThrow(ValidationException::class);

    Mail::assertNothingSent();
    expect($inscrito->fresh()->certificado_envio_id)->toBe($job->tentativaId);
})->with([['', null], ['inválido', null], ['valido@example.com', 'inválido']]);

it('libera pendência e cache após falha definitiva e permite uma nova tentativa', function () {
    $inscrito = inscritoCertificadoInterlab();
    app(EnfileirarCertificadoInterlabPendenteAction::class)->execute($inscrito->id);
    $job = jobCertificadoInterlab();
    Cache::put(EnviarCertificadoInterlabPendenteCommand::SEM_CERTIFICADO_PENDENTE_CACHE_KEY, true, 600);

    $job->failed(new RuntimeException('SMTP indisponível'));

    expect($inscrito->fresh()->certificado_emitido)->toBeNull();
    expect($inscrito->fresh()->certificado_envio_id)->toBeNull();
    expect(Cache::has(EnviarCertificadoInterlabPendenteCommand::SEM_CERTIFICADO_PENDENTE_CACHE_KEY))->toBeFalse();
    expect(file_get_contents($this->certificadosLog))->toContain('Falha definitiva', 'SMTP indisponível', $job->tentativaId);

    app(EnfileirarCertificadoInterlabPendenteAction::class)->execute($inscrito->id);
    $tentativaAtual = $inscrito->fresh()->certificado_envio_id;
    Mail::fake();
    $job->handle();
    $job->failed(new RuntimeException('Falha antiga'));

    expect($inscrito->fresh()->certificado_envio_id)->toBe($tentativaAtual);
    expect($inscrito->fresh()->certificado_emitido)->not->toBeNull();
    Mail::assertNothingSent();
});

it('preserva um envio manual que substituiu a tentativa automática', function () {
    $inscrito = inscritoCertificadoInterlab();
    app(EnfileirarCertificadoInterlabPendenteAction::class)->execute($inscrito->id);
    $antigo = jobCertificadoInterlab();
    Queue::fake();
    Mail::fake();
    $this->travel(1)->minutes();
    app(EnviarCertificadoInterlabAction::class)->execute($inscrito->fresh());
    $dataManual = $inscrito->fresh()->certificado_emitido;

    $antigo->handle();
    $antigo->failed(new RuntimeException('Falha antiga'));

    expect($inscrito->fresh()->certificado_emitido)->toBe($dataManual);
    expect($inscrito->fresh()->certificado_envio_id)->toBeNull();
    Mail::assertNothingSent();
});

it('preserva a tentativa automática se o enfileiramento manual falhar', function () {
    $inscrito = inscritoCertificadoInterlab();
    app(EnfileirarCertificadoInterlabPendenteAction::class)->execute($inscrito->id);
    $original = $inscrito->fresh()->getAttributes();
    Bus::shouldReceive('dispatch')->once()->andThrow(new RuntimeException('Fila indisponível'));

    expect(fn () => app(EnviarCertificadoInterlabAction::class)->execute($inscrito))
        ->toThrow(RuntimeException::class, 'Fila indisponível');

    expect($inscrito->fresh()->getAttributes())->toBe($original);
    $this->assertDatabaseCount('dados_gera_doc', 1);
    $this->assertDatabaseCount('jobs', 1);
});

it('enfileira o manual Interlab na mesma transação com o destinatário validado', function () {
    Mail::fake();
    $inscrito = inscritoCertificadoInterlab();

    $doc = app(EnviarCertificadoInterlabAction::class)->execute($inscrito, ' Manual@example.com ');

    $payload = json_decode(DB::table('jobs')->value('payload'), true);
    $job = unserialize($payload['data']['command']);
    expect($job)->toBeInstanceOf(GerarCertificadoInterlabJob::class);
    expect($doc->content['laboratorio_email'])->toBe('Manual@example.com');
    expect($inscrito->fresh()->certificado_emitido)->not->toBeNull();
    expect($inscrito->fresh()->certificado_envio_id)->toBeNull();

    $job->handle();

    Mail::assertSent(CertificadoInterlabMail::class, fn ($mail) => $mail->hasTo('Manual@example.com'));
    Mail::assertSentCount(1);
});

it('suspende consultas e envia somente um aviso até a próxima meia-noite em São Paulo', function () {
    Mail::shouldReceive('raw')->once()->with(
        'Não há certificados Interlab aptos para envio no momento.',
        Mockery::on(function (Closure $callback): bool {
            $message = new Illuminate\Mail\Message(new Symfony\Component\Mime\Email);
            $callback($message);

            return $message->getSymfonyMessage()->getTo()[0]->getAddress() === 'sistema@redemetrologica.com.br';
        }),
    );
    $this->travelTo(new DateTimeImmutable('2026-10-23 05:59:00-03:00'));
    $this->artisan('interlab:enviar-certificado-pendente')->assertSuccessful();
    $inscrito = inscritoCertificadoInterlab();

    $this->artisan('interlab:enviar-certificado-pendente')->assertSuccessful();

    expect($inscrito->fresh()->certificado_emitido)->toBeNull();
    $this->travelTo(new DateTimeImmutable('2026-10-24 00:00:01-03:00'));

    $this->artisan('interlab:enviar-certificado-pendente')->assertSuccessful();

    expect($inscrito->fresh()->certificado_emitido)->not->toBeNull();
});

it('backfill preserva envios manuais e demais campos e só marca concluídos pagos', function () {
    Mail::fake();
    Queue::fake();
    $elegivel = inscritoCertificadoInterlab([
        'email' => null,
        'certificado_path' => 'manter.pdf',
    ]);
    $elegivel->pessoa->update(['email' => null]);
    $elegivel->update(['updated_at' => '2025-01-01 12:00:00']);
    $original = $elegivel->fresh()->getAttributes();
    $manual = inscritoCertificadoInterlab(['certificado_emitido' => '2025-01-01 12:00:00']);
    $originalManual = $manual->fresh()->getAttributes();
    $confirmado = inscritoCertificadoInterlab([], 'CONFIRMADO');
    $naoPago = inscritoCertificadoInterlab([], 'CONCLUIDO', 'PROVISIONADO');
    $semLancamento = inscritoCertificadoInterlab([], 'CONCLUIDO', null);

    $this->artisan('interlab:backfill-certificados-emitidos')->expectsOutput('Certificados marcados: 1.')->assertSuccessful();

    $atual = $elegivel->fresh()->getAttributes();
    expect($atual['certificado_emitido'])->toBe(now()->format('Y-m-d H:i:s'));
    expect($atual['updated_at'])->toBe($atual['certificado_emitido']);
    $aposBackfill = $atual;
    unset($atual['certificado_emitido'], $original['certificado_emitido'], $atual['updated_at'], $original['updated_at']);
    expect($atual)->toBe($original);
    expect($manual->fresh()->certificado_emitido)->toBe('2025-01-01 12:00:00');
    expect($manual->fresh()->getAttributes())->toBe($originalManual);
    foreach ([$confirmado, $naoPago, $semLancamento] as $ignorado) {
        expect($ignorado->fresh()->certificado_emitido)->toBeNull();
    }
    $this->travel(1)->minutes();
    $this->artisan('interlab:backfill-certificados-emitidos')->expectsOutput('Certificados marcados: 0.')->assertSuccessful();
    expect($elegivel->fresh()->getAttributes())->toBe($aposBackfill);
    expect($manual->fresh()->getAttributes())->toBe($originalManual);
    $this->assertDatabaseCount('dados_gera_doc', 0);
    Mail::assertNothingSent();
    Queue::assertNothingPushed();
});

it('dry run não altera registros', function () {
    $inscrito = inscritoCertificadoInterlab();
    $original = $inscrito->fresh()->getAttributes();

    $this->artisan('interlab:backfill-certificados-emitidos', ['--dry-run' => true])
        ->expectsOutput('Inscrições elegíveis: 1. Nenhum registro alterado.')->assertSuccessful();

    expect($inscrito->fresh()->getAttributes())->toBe($original);
    $this->assertDatabaseCount('jobs', 0);
    $this->assertDatabaseCount('dados_gera_doc', 0);
});

it('preserva envio manual ocorrido imediatamente antes do update do backfill', function () {
    $inscrito = inscritoCertificadoInterlab();
    $interceptado = false;
    DB::connection()->beforeExecuting(function (string $sql) use ($inscrito, &$interceptado): void {
        if (! $interceptado && str_starts_with($sql, 'update "interlab_inscritos" set "certificado_emitido"')) {
            $interceptado = true;
            DB::table('interlab_inscritos')->where('id', $inscrito->id)
                ->update(['certificado_emitido' => '2026-10-22 23:59:59']);
        }
    });

    $this->artisan('interlab:backfill-certificados-emitidos')
        ->expectsOutput('Certificados marcados: 0.')->assertSuccessful();

    expect($interceptado)->toBeTrue();
    expect($inscrito->fresh()->certificado_emitido)->toBe('2026-10-22 23:59:59');
});

it('worker retenta três vezes e registra falha definitiva liberando a inscrição', function () {
    $inscrito = inscritoCertificadoInterlab();
    app(EnfileirarCertificadoInterlabPendenteAction::class)->execute($inscrito->id);
    $tentativa = jobCertificadoInterlab()->tentativaId;
    Mail::shouldReceive('to')->with('tecnico@example.com')->times(3)->andThrow(new RuntimeException('SMTP de teste indisponível'));
    Mail::shouldReceive('to')->with('sistema@redemetrologica.com.br')->andReturnSelf();
    Mail::shouldReceive('send')->andReturnNull();
    config(['logging.channels.single' => config('logging.channels.null')]);

    for ($tentativaNumero = 1; $tentativaNumero <= 3; $tentativaNumero++) {
        $this->artisan('queue:work', ['connection' => 'database', '--once' => true, '--sleep' => 0])->assertSuccessful();

        if ($tentativaNumero < 3) {
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
    expect(file_get_contents($this->certificadosLog))->toContain('Falha definitiva', $tentativa);
});

it('agenda somente na janela de São Paulo e respeita o bloqueio de pendências', function () {
    $event = collect(app(Schedule::class)->events())->first(fn ($event) => str_contains($event->command ?? '', 'interlab:enviar-certificado-pendente'));

    expect($event)->not->toBeNull();
    expect($event->expression)->toBe('* * * * *');
    expect($event->timezone)->toBe('America/Sao_Paulo');
    expect($event->withoutOverlapping)->toBeTrue();
    foreach (['00:00:00' => true, '06:00:00' => true, '06:01:00' => false, '23:59:00' => false] as $horario => $permitido) {
        $this->travelTo(new DateTimeImmutable("2026-10-23 {$horario}-03:00"));
        $schedule = app(Illuminate\Contracts\Console\Kernel::class)->resolveConsoleSchedule();
        $event = collect($schedule->events())->first(fn ($event) => str_contains($event->command ?? '', 'interlab:enviar-certificado-pendente'));
        expect($event->filtersPass(app()))->toBe($permitido);
    }

    $this->travelTo(new DateTimeImmutable('2026-10-23 02:00:00-03:00'));
    $schedule = app(Illuminate\Contracts\Console\Kernel::class)->resolveConsoleSchedule();
    $event = collect($schedule->events())->first(fn ($event) => str_contains($event->command ?? '', 'interlab:enviar-certificado-pendente'));
    Cache::put(EnviarCertificadoInterlabPendenteCommand::SEM_CERTIFICADO_PENDENTE_CACHE_KEY, true, 600);
    expect($event->filtersPass(app()))->toBeFalse();
});

it('não envia se agenda ou pagamento mudarem após o enfileiramento', function (string $relacao, string $status) {
    Mail::fake();
    $inscrito = inscritoCertificadoInterlab();
    app(EnfileirarCertificadoInterlabPendenteAction::class)->execute($inscrito->id);
    $job = jobCertificadoInterlab();
    $inscrito->{$relacao}->update(['status' => $status]);

    expect(fn () => $job->handle())->toThrow(RuntimeException::class, 'A inscrição não possui mais agenda concluída e pagamento efetivado.');

    Mail::assertNothingSent();
    $job->failed(new RuntimeException('Inscrição deixou de ser elegível'));
    expect($inscrito->fresh()->certificado_emitido)->toBeNull();
})->with([['agendaInterlab', 'CONFIRMADO'], ['lancamentoFinanceiro', 'PROVISIONADO']]);

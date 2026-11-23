<?php

use App\Jobs\EnviarEmailAgendadoJob;
use App\Queue\ScheduledEmailGate;
use App\Support\EnviarEmailAgendado;
use App\Support\ProcessosAgendados;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Monolog\Handler\TestHandler;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;

class MensagemDeTesteAgendada extends Mailable
{
    public function build(): self
    {
        return $this->subject('Teste de fila')->html('<p>Teste</p>');
    }
}

class TransporteDeTesteAgendado extends AbstractTransport
{
    public array $aceites = [];

    public bool $falharNoProximoEnvio = false;

    protected function doSend(SentMessage $message): void
    {
        if ($this->falharNoProximoEnvio) {
            $this->falharNoProximoEnvio = false;

            throw new TransportException('Falha de transporte simulada.');
        }

        $this->aceites[] = now()->getTimestamp();
    }

    public function __toString(): string
    {
        return 'teste-agendado';
    }
}

function processarProximoJobAgendado(): void
{
    test()->artisan('queue:work', [
        'connection' => 'database',
        '--name' => 'sistema-rede',
        '--queue' => ProcessosAgendados::FILA_EMAIL.',default',
        '--once' => true,
        '--sleep' => 0,
        '--tries' => 3,
    ])->assertSuccessful();
}

it('mantém o intervalo de aceite após reiniciar o worker e atende default durante a pausa', function () {
    $this->travelTo(now()->startOfSecond());
    $inicio = now()->getTimestamp();
    $transporte = new TransporteDeTesteAgendado;
    Mail::mailer()->setSymfonyTransport($transporte);

    foreach (range(1, 2) as $i) {
        EnviarEmailAgendadoJob::dispatch(new MensagemDeTesteAgendada, "teste{$i}@example.test");
    }

    processarProximoJobAgendado();
    $this->travel(4)->seconds();
    dispatch(static function () {})->onConnection('database')->onQueue('default');
    app()->forgetInstance(ScheduledEmailGate::class);
    processarProximoJobAgendado();

    expect($transporte->aceites)->toBe([$inicio])
        ->and(DB::table('jobs')->where('queue', 'default')->count())->toBe(0)
        ->and((int) DB::table('jobs')->where('queue', ProcessosAgendados::FILA_EMAIL)->value('attempts'))->toBe(0);

    $this->travel(1)->seconds();
    processarProximoJobAgendado();

    expect($transporte->aceites)->toBe([$inicio, $inicio + 5])
        ->and(DB::table('jobs')->count())->toBe(0);
});

it('aguarda cinco segundos após falha antes de tentar o envio de novo', function () {
    Exceptions::fake(TransportException::class);
    $this->travelTo(now()->startOfSecond());
    $inicio = now()->getTimestamp();
    $transporte = new TransporteDeTesteAgendado;
    $transporte->falharNoProximoEnvio = true;
    Mail::mailer()->setSymfonyTransport($transporte);
    EnviarEmailAgendadoJob::dispatch(new MensagemDeTesteAgendada, 'teste@example.test');

    processarProximoJobAgendado();
    Exceptions::assertReported(TransportException::class);

    $this->travel(4)->seconds();
    processarProximoJobAgendado();

    $pendente = DB::table('jobs')->where('queue', ProcessosAgendados::FILA_EMAIL)->first();
    expect($transporte->aceites)->toBe([])
        ->and((int) $pendente->attempts)->toBe(1)
        ->and($pendente->reserved_at)->toBeNull();

    $this->travel(1)->seconds();
    processarProximoJobAgendado();

    expect($transporte->aceites)->toBe([$inicio + 5])
        ->and(DB::table('jobs')->count())->toBe(0)
        ->and(DB::table('failed_jobs')->count())->toBe(0);
});

it('respeita a reserva persistida quando um worker termina antes de concluir o job', function () {
    $this->travelTo(now()->startOfSecond());
    $inicio = now()->getTimestamp();
    $transporte = new TransporteDeTesteAgendado;
    Mail::mailer()->setSymfonyTransport($transporte);
    EnviarEmailAgendadoJob::dispatch(new MensagemDeTesteAgendada, 'teste@example.test');

    $reservado = app('queue')->connection('database')->pop(ProcessosAgendados::FILA_EMAIL);
    expect($reservado->attempts())->toBe(1);
    app()->forgetInstance(ScheduledEmailGate::class);

    $this->travel(364)->seconds();
    processarProximoJobAgendado();

    $pendente = DB::table('jobs')->where('queue', ProcessosAgendados::FILA_EMAIL)->first();
    expect($transporte->aceites)->toBe([])
        ->and((int) $pendente->attempts)->toBe(1)
        ->and((int) $pendente->reserved_at)->toBe($inicio);

    $this->travel(1)->seconds();
    processarProximoJobAgendado();

    expect($transporte->aceites)->toBe([$inicio + 365])
        ->and(DB::table('jobs')->count())->toBe(0);
});

it('registra o enfileiramento após a transação e correlaciona o aceite pelo UUID do job', function () {
    $registros = new TestHandler;
    Log::channel('processos_agendados')->getLogger()->setHandlers([$registros]);
    Mail::mailer()->setSymfonyTransport(new TransporteDeTesteAgendado);

    DB::transaction(function () use ($registros) {
        ProcessosAgendados::executar(['execucao_id' => 'execucao-teste'], function () {
            EnviarEmailAgendadoJob::dispatch(new MensagemDeTesteAgendada, 'teste@example.test');
        });

        expect($registros->getRecords())->toBe([]);
    });

    processarProximoJobAgendado();

    [$enfileirado, $aceito] = $registros->getRecords();
    expect($enfileirado->message)->toBe('email.enfileirado')
        ->and($aceito->message)->toBe('email.aceito')
        ->and($enfileirado->context['job_id'])->not->toBeNull()
        ->and($enfileirado->context['job_uuid'])->toBe($aceito->context['job_uuid'])
        ->and($enfileirado->context['execucao_id'])->toBe('execucao-teste')
        ->and($aceito->context['execucao_id'])->toBe('execucao-teste');
});

it('recusa um transporte local para e-mails agendados', function () {
    config(['mail.default' => 'array']);
    Mail::purge();

    expect(fn () => app(EnviarEmailAgendado::class)
        ->execute(new MensagemDeTesteAgendada, 'teste@example.test'))
        ->toThrow(RuntimeException::class, 'confirme o aceite');
});

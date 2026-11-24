<?php

use App\Mail\AvisoSemCertificadosPendentesMail;
use App\Support\ProcessosAgendados;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Monolog\Handler\TestHandler;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;

class AvisoAgendadoComRetentativa extends AvisoSemCertificadosPendentesMail
{
    public function backoff(): int
    {
        return 12;
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

function enfileirarAvisoParaTesteDaFila(): void
{
    $email = (new AvisoAgendadoComRetentativa('Teste', 'Mensagem de teste.'))
        ->onConnection('database');

    ProcessosAgendados::enfileirar(fn ($horario) => Mail::to('teste@example.test')->later($horario, $email));
}

function processarProximoJobAgendado(): void
{
    test()->artisan('queue:work', [
        'connection' => 'database',
        '--name' => 'sistema-rede',
        '--queue' => 'default',
        '--once' => true,
        '--sleep' => 0,
        '--tries' => 3,
    ])->assertSuccessful();
}

it('registra somente os e-mails agendados ao processar jobs misturados na fila default', function () {
    $inicio = CarbonImmutable::now('America/Sao_Paulo')->startOfSecond();
    $this->travelTo($inicio);
    $registros = new TestHandler;
    Log::channel('processos_agendados')->getLogger()->setHandlers([$registros]);
    $transporte = new TransporteDeTesteAgendado;
    Mail::mailer()->setSymfonyTransport($transporte);

    ProcessosAgendados::rodada($inicio, fn (): int => ProcessosAgendados::execucao('certificados:enviar-pendente', function (): int {
        enfileirarAvisoParaTesteDaFila();
        dispatch(static function (): void {})->onConnection('database');
        enfileirarAvisoParaTesteDaFila();

        return 0;
    }));

    $this->travel(20)->seconds();
    processarProximoJobAgendado();
    processarProximoJobAgendado();
    processarProximoJobAgendado();

    $inicios = collect($registros->getRecords())->where('message', 'email.job.inicio')->values();
    $finais = collect($registros->getRecords())->where('message', 'email.job.fim')->values();

    expect($transporte->aceites)->toBe(array_fill(0, 2, $inicio->getTimestamp() + 20))
        ->and($inicios->pluck('context.atraso_segundos')->all())->toBe([20, 15])
        ->and($inicios->pluck('context.job_uuid')->all())->toBe($finais->pluck('context.job_uuid')->all())
        ->and($finais->pluck('context.resultado')->all())->toBe(['concluido', 'concluido'])
        ->and($inicios->pluck('extra.processo')->unique()->all())->toBe(['certificados:enviar-pendente'])
        ->and($inicios->pluck('extra.rodada_id')->filter()->unique()->count())->toBe(1)
        ->and(DB::table('jobs')->count())->toBe(0);
});

it('mede a retentativa usando o horário da própria tentativa', function () {
    Exceptions::fake(TransportException::class);
    $inicio = CarbonImmutable::now('America/Sao_Paulo')->startOfSecond();
    $this->travelTo($inicio);
    $registros = new TestHandler;
    Log::channel('processos_agendados')->getLogger()->setHandlers([$registros]);
    $transporte = new TransporteDeTesteAgendado;
    $transporte->falharNoProximoEnvio = true;
    Mail::mailer()->setSymfonyTransport($transporte);

    ProcessosAgendados::rodada($inicio, function (): int {
        enfileirarAvisoParaTesteDaFila();

        return 0;
    });

    processarProximoJobAgendado();
    Exceptions::assertReported(TransportException::class);
    $this->travel(15)->seconds();
    processarProximoJobAgendado();

    $inicios = collect($registros->getRecords())->where('message', 'email.job.inicio')->values();
    $finais = collect($registros->getRecords())->where('message', 'email.job.fim')->values();
    expect($inicios->pluck('context.tentativa')->all())->toBe([1, 2])
        ->and($inicios->pluck('context.available_at')->all())->toBe([$inicio->getTimestamp(), $inicio->getTimestamp() + 12])
        ->and($inicios->pluck('context.atraso_segundos')->all())->toBe([0, 3])
        ->and($inicios->pluck('context.job_uuid')->unique()->count())->toBe(1)
        ->and($finais->pluck('context.resultado')->all())->toBe(['retentativa', 'concluido']);
});

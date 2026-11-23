<?php

namespace App\Jobs\Interlab;

use App\Actions\Interlab\RastrearItemLotePostagemAction;
use App\Exceptions\CorreiosApiException;
use App\Integrations\Correios\CorreiosLog;
use App\Jobs\EnviarEmailAgendadoJob;
use App\Mail\InterlabLotePostagemRastreioMail;
use App\Models\InterlabLotePostagemItem;
use App\Support\ProcessosAgendados;
use DateTime;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Queue\Jobs\FakeJob;
use Illuminate\Support\Facades\Mail;
use Throwable;

class RastrearItemLotePostagemJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $timeout = 120;

    public int $uniqueFor = 86400;

    private ?array $contextoAgendado = null;

    public function __construct(public InterlabLotePostagemItem $item, ?array $contextoAgendado = null)
    {
        $this->contextoAgendado = $contextoAgendado;
    }

    public function uniqueId(): string
    {
        return (string) $this->item->id;
    }

    public function retryUntil(): DateTime
    {
        return now()->addDay()->toDateTime();
    }

    public function handle(RastrearItemLotePostagemAction $action): void
    {
        if ($this->contextoAgendado === null) {
            $this->rastrear($action);

            return;
        }

        ProcessosAgendados::executar([
            ...$this->contextoAgendado,
            'item_id' => $this->item->id,
        ], fn () => $this->rastrear($action));
    }

    private function rastrear(RastrearItemLotePostagemAction $action): void
    {
        $item = $this->item->fresh(['inscrito']);

        if ($item === null) {
            CorreiosLog::warning('job.rastro.item_ausente', [
                ...ProcessosAgendados::contexto(),
                'item_id' => $this->item->id,
            ]);
            $this->logAgendado('rastreio.ignorado', ['motivo' => 'item_ausente'], 'warning');

            return;
        }

        $statusAnterior = $item->status_correios;

        CorreiosLog::info('job.rastro.inicio', [
            ...ProcessosAgendados::contexto(),
            'item_id' => $item->id,
            'codigo_objeto' => $item->codigo_objeto,
            'status_correios_anterior' => $statusAnterior,
            'tentativa' => $this->attempts(),
        ]);
        $this->logAgendado('rastreio.inicio', ['tentativa' => $this->attempts()]);

        try {
            $item = $action->execute($item);
        } catch (ConnectionException $exception) {
            $this->tratarFalhaTransitoria($item, $exception);

            return;
        } catch (CorreiosApiException $exception) {
            if ($this->eFalhaTransitoria($exception->status)) {
                $this->tratarFalhaTransitoria($item, $exception);

                return;
            }

            CorreiosLog::error('job.rastro.falha_definitiva', [
                ...ProcessosAgendados::contexto(),
                'item_id' => $item->id,
                'codigo_objeto' => $item->codigo_objeto,
                'status_http' => $exception->status,
                'mensagem' => $exception->getMessage(),
            ]);
            $this->logAgendado('rastreio.falha', [
                'status_http' => $exception->status,
                'exception' => $exception::class,
            ], 'error');

            return;
        }

        $this->notificarSeMudou($item->fresh(['inscrito']) ?? $item, $statusAnterior);
        $this->logAgendado('rastreio.concluido');
    }

    public function failed(?Throwable $exception): void
    {
        CorreiosLog::exception('job.rastro.failed', $exception ?? new \RuntimeException('Falha no rastreio.'), [
            ...($this->contextoAgendado ?? []),
            'item_id' => $this->item->id,
            'codigo_objeto' => $this->item->codigo_objeto,
        ]);
        $this->logAgendado('rastreio.falha', [
            'exception' => $exception ? $exception::class : null,
        ], 'error');
    }

    private function notificarSeMudou(InterlabLotePostagemItem $item, ?string $statusAnterior): void
    {
        $statusAtual = $item->status_correios;

        if ($statusAtual === null || $statusAtual === $statusAnterior) {
            CorreiosLog::info('job.rastro.sem_notificacao', [
                ...ProcessosAgendados::contexto(),
                'item_id' => $item->id,
                'status_correios' => $statusAtual,
                'motivo' => $statusAtual === null ? 'sem_status' : 'inalterado',
            ]);

            return;
        }

        $email = trim((string) ($item->inscrito?->email ?? ''));

        if ($email === '') {
            CorreiosLog::warning('job.rastro.email_ausente', [
                ...ProcessosAgendados::contexto(),
                'item_id' => $item->id,
                'inscrito_id' => $item->interlab_inscrito_id,
            ]);

            return;
        }

        /** @var list<string> $copias */
        $copias = config('services.correios.documentos_email', []);

        $notificacao = new InterlabLotePostagemRastreioMail($item);

        if ($this->contextoAgendado !== null) {
            EnviarEmailAgendadoJob::dispatch($notificacao, $email, ProcessosAgendados::contexto(), $copias);
            ProcessosAgendados::log('rastreio.email.enfileirado');
        } else {
            Mail::to($email)->cc($copias)->queue($notificacao);
        }

        CorreiosLog::info('job.rastro.email_enfileirado', [
            ...ProcessosAgendados::contexto(),
            'item_id' => $item->id,
            'status_correios' => $statusAtual,
            'status_anterior' => $statusAnterior,
        ]);
    }

    private function eFalhaTransitoria(int $status): bool
    {
        return $status === 429 || $status >= 500;
    }

    private function tratarFalhaTransitoria(InterlabLotePostagemItem $item, Throwable $exception): void
    {
        $atraso = min(2 ** max(0, $this->attempts() - 1), 300);

        CorreiosLog::warning('job.rastro.retentar', [
            ...ProcessosAgendados::contexto(),
            'item_id' => $item->id,
            'codigo_objeto' => $item->codigo_objeto,
            'atraso_segundos' => $atraso,
            'mensagem' => $exception->getMessage(),
        ]);

        if (! $this->job instanceof FakeJob && config('queue.default') === 'sync') {
            CorreiosLog::info('job.rastro.sem_release_sync', [
                ...ProcessosAgendados::contexto(),
                'item_id' => $item->id,
            ]);

            return;
        }

        $this->release($atraso);
        $this->logAgendado('rastreio.retentativa', [
            'atraso_segundos' => $atraso,
            'exception' => $exception::class,
        ], 'warning');
    }

    private function logAgendado(string $evento, array $contexto = [], string $nivel = 'info'): void
    {
        if ($this->contextoAgendado !== null) {
            ProcessosAgendados::log($evento, [
                ...$this->contextoAgendado,
                'item_id' => $this->item->id,
                ...$contexto,
            ], $nivel);
        }
    }
}

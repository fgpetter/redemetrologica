<?php

namespace App\Jobs\Interlab;

use App\Actions\Interlab\ClassificarServicoItensLotePostagemCorreiosAction;
use App\Actions\Interlab\EncerrarInterlabLotePostagemComErroAction;
use App\Enums\InterlabLotePostagemItemStatus;
use App\Enums\InterlabLotePostagemStatus;
use App\Integrations\Correios\CorreiosLog;
use App\Models\InterlabLotePostagem;
use DateTime;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Jobs\FakeJob;
use RuntimeException;
use Throwable;

class ClassificarServicoLotePostagemJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $timeout = 120;

    public int $uniqueFor = 86400;

    public function __construct(public InterlabLotePostagem $lote) {}

    public function uniqueId(): string
    {
        return (string) $this->lote->id;
    }

    public function retryUntil(): DateTime
    {
        return now()->addDay()->toDateTime();
    }

    public function handle(ClassificarServicoItensLotePostagemCorreiosAction $action): void
    {
        $lote = $this->lote->fresh();

        if ($lote === null) {
            CorreiosLog::warning('job.classificacao.lote_ausente', [
                'lote_id' => $this->lote->id,
            ]);

            return;
        }

        CorreiosLog::info('job.classificacao.inicio', [
            'lote_id' => $lote->id,
            'lote_uid' => $lote->uid,
            'tentativa' => $this->attempts(),
        ]);

        $resultado = $action->execute($lote, $this->attempts());

        CorreiosLog::info('job.classificacao.resultado', [
            'lote_id' => $lote->id,
            'concluida' => $resultado->estaConcluida(),
            'retentar' => $resultado->deveRetentar(),
            'falhou' => $resultado->estaFalhou(),
            'atraso_segundos' => $resultado->atrasoSegundos,
        ]);

        if ($resultado->estaConcluida()) {
            $this->enfileirarProcessamento($lote->fresh() ?? $lote);

            return;
        }

        if (! $resultado->deveRetentar()) {
            return;
        }

        if (! $this->job instanceof FakeJob && config('queue.default') === 'sync') {
            CorreiosLog::info('job.classificacao.sem_release_sync', [
                'lote_id' => $lote->id,
            ]);

            return;
        }

        $this->release($resultado->atrasoSegundos);
    }

    private function enfileirarProcessamento(InterlabLotePostagem $lote): void
    {
        if ($lote->status !== InterlabLotePostagemStatus::ClassificandoServico) {
            return;
        }

        $pendentes = $lote->itens()
            ->where('status', '!=', InterlabLotePostagemItemStatus::Classificado->value)
            ->exists();

        if ($pendentes || ! $lote->itens()->exists()) {
            return;
        }

        CorreiosLog::info('processar.handoff', [
            'lote_id' => $lote->id,
            'lote_uid' => $lote->uid,
            'de' => 'classificacao',
            'para' => 'processar',
        ]);

        ProcessarInterlabLotePostagemJob::dispatch($lote);
    }

    public function failed(?Throwable $exception): void
    {
        $lote = $this->lote->fresh();

        if ($lote === null || $lote->status !== InterlabLotePostagemStatus::ClassificandoServico) {
            CorreiosLog::warning('job.classificacao.failed_ignorado', [
                'lote_id' => $this->lote->id,
                'status' => $lote?->status->value,
                'mensagem' => $exception?->getMessage(),
            ]);

            return;
        }

        CorreiosLog::exception('job.classificacao.failed', $exception ?? new RuntimeException('Falha inesperada na classificação de serviço.'), [
            'lote_id' => $lote->id,
        ]);

        app(EncerrarInterlabLotePostagemComErroAction::class)->execute(
            $lote,
            InterlabLotePostagemStatus::ClassificandoServico->value,
            $exception?->getMessage() ?? 'Falha inesperada na classificação de serviço.',
        );
    }
}

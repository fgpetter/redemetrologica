<?php

namespace App\Jobs\Interlab;

use App\Actions\Interlab\EncerrarInterlabLotePostagemComErroAction;
use App\Actions\Interlab\ProcessarInterlabLotePostagemAction;
use App\Enums\InterlabLotePostagemStatus;
use App\Integrations\Correios\CorreiosLog;
use App\Models\InterlabLotePostagem;
use App\Support\Observabilidade\ResumoExecucao;
use DateTime;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Jobs\FakeJob;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class ProcessarInterlabLotePostagemJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $timeout = 300;

    public int $uniqueFor = 600;

    public function __construct(public InterlabLotePostagem $lote) {}

    public function uniqueId(): string
    {
        return (string) $this->lote->id;
    }

    public function retryUntil(): DateTime
    {
        return now()->addDay()->toDateTime();
    }

    public function handle(ProcessarInterlabLotePostagemAction $action): void
    {
        $resumo = new ResumoExecucao('correios.ProcessarInterlabLotePostagemJob', ['tentativa' => $this->attempts(), 'lote_uid' => $this->lote->uid], 'correios');
        $resumo->executar(function ($resumo) use ($action): void {
            $lote = $this->lote->fresh();

            if ($lote === null) {
                $resumo->resultado('ignorado', 'lote_ausente');
                CorreiosLog::warning('job.processar.lote_ausente', [
                    'lote_id' => $this->lote->id,
                ]);

                return;
            }

            CorreiosLog::info('job.processar.inicio', [
                'lote_id' => $lote->id,
                'lote_uid' => $lote->uid,
                'status' => $lote->status->value,
                'tentativa' => $this->attempts(),
            ]);

            $statusAnterior = $lote->status;
            $resultado = $resumo->etapa('executar_etapas', fn () => $action->execute($lote, $this->attempts(), $resumo));
            $resumo->resultadoEtapa('executar_etapas', $resultado->estaFalhou() ? 'falha_definitiva' : ($resultado->deveRetentar() ? ($resultado->motivo === 'falha_transitoria' ? 'retentativa' : 'encaminhado') : 'concluido'));
            $resumo->contexto(['status' => $lote->fresh()?->status->value]);
            $resumo->resultado($resultado->estaFalhou() ? 'falha_definitiva' : ($resultado->deveRetentar() ? ($resultado->motivo === 'falha_transitoria' ? 'retentativa' : 'encaminhado') : 'concluido'), $resultado->motivo);

            if (in_array($statusAnterior, [InterlabLotePostagemStatus::Concluido, InterlabLotePostagemStatus::Erro], true)) {
                $resumo->resultado('ignorado', 'lote_encerrado');
            } elseif ($resultado->estaConcluida() && $lote->fresh()?->status !== InterlabLotePostagemStatus::Concluido) {
                $resumo->resultado('aguardando_continuidade');
            }

            if ($statusAnterior !== InterlabLotePostagemStatus::Concluido && $lote->fresh()?->status === InterlabLotePostagemStatus::Concluido) {
                DB::afterCommit(fn () => CorreiosLog::concluido(['lote_uid' => $lote->uid]));
            }

            CorreiosLog::info('processar.resultado', [
                'lote_id' => $lote->id,
                'concluida' => $resultado->estaConcluida(),
                'retentar' => $resultado->deveRetentar(),
                'falhou' => $resultado->estaFalhou(),
                'atraso_segundos' => $resultado->atrasoSegundos,
                'status' => $lote->fresh()?->status->value,
            ]);

            if (! $resultado->deveRetentar()) {
                return;
            }

            if (! $this->job instanceof FakeJob && config('queue.default') === 'sync') {
                CorreiosLog::info('job.processar.sem_release_sync', [
                    'lote_id' => $lote->id,
                ]);

                return;
            }

            $this->release($resultado->atrasoSegundos);
        }, job: true);
    }

    public function failed(?Throwable $exception): void
    {
        $lote = $this->lote->fresh();

        if ($lote === null || ! $lote->status->emProcessamento()) {
            CorreiosLog::warning('job.processar.failed_ignorado', [
                'lote_id' => $this->lote->id,
                'status' => $lote?->status->value,
                'mensagem' => $exception?->getMessage(),
            ]);

            return;
        }

        CorreiosLog::exception('job.processar.failed', $exception ?? new RuntimeException('Falha inesperada no processamento do lote.'), [
            'lote_id' => $lote->id,
        ]);

        app(EncerrarInterlabLotePostagemComErroAction::class)->execute(
            $lote,
            $lote->status->value,
            $exception?->getMessage() ?? 'Falha inesperada no processamento do lote.',
        );
    }
}

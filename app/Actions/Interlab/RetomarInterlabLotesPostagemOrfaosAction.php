<?php

namespace App\Actions\Interlab;

use App\Enums\InterlabLotePostagemItemStatus;
use App\Enums\InterlabLotePostagemStatus;
use App\Integrations\Correios\CorreiosLog;
use App\Jobs\Interlab\ProcessarInterlabLotePostagemJob;
use App\Models\InterlabLotePostagem;
use App\Models\InterlabLotePostagemItem;
use Illuminate\Bus\UniqueLock;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RetomarInterlabLotesPostagemOrfaosAction
{
    public function __construct(private CacheRepository $cache) {}

    /**
     * @return array{enfileirados: int, pulados: int}
     */
    public function execute(): array
    {
        $enfileirados = 0;
        $pulados = 0;

        foreach ($this->lotesCandidatos() as $lote) {
            if ($this->jobNaFila($lote)) {
                CorreiosLog::info('processar.orfao.skip', [
                    'lote_id' => $lote->id,
                    'lote_uid' => $lote->uid,
                    'status' => $lote->status->value,
                    'motivo' => 'job_na_fila',
                ]);
                $pulados++;

                continue;
            }

            $job = new ProcessarInterlabLotePostagemJob($lote);
            (new UniqueLock($this->cache))->release($job);
            ProcessarInterlabLotePostagemJob::dispatch($lote);

            CorreiosLog::info('processar.orfao.enfileirado', [
                'lote_id' => $lote->id,
                'lote_uid' => $lote->uid,
                'status' => $lote->status->value,
            ]);
            $enfileirados++;
        }

        return [
            'enfileirados' => $enfileirados,
            'pulados' => $pulados,
        ];
    }

    /**
     * @return list<InterlabLotePostagem>
     */
    private function lotesCandidatos(): array
    {
        return InterlabLotePostagem::query()
            ->with('itens')
            ->emProcessamento()
            ->orderBy('id')
            ->get()
            ->filter(fn (InterlabLotePostagem $lote): bool => $this->eCandidato($lote))
            ->values()
            ->all();
    }

    private function eCandidato(InterlabLotePostagem $lote): bool
    {
        if (in_array($lote->status, [
            InterlabLotePostagemStatus::GerandoPrePostagens,
            InterlabLotePostagemStatus::GerandoEtiquetas,
            InterlabLotePostagemStatus::GerandoDeclaracao,
        ], true)) {
            return true;
        }

        if ($lote->status !== InterlabLotePostagemStatus::ClassificandoServico) {
            return false;
        }

        $itens = $lote->itens;

        return $itens->isNotEmpty()
            && $itens->every(fn (InterlabLotePostagemItem $item): bool => $item->status === InterlabLotePostagemItemStatus::Classificado
                && filled($item->codigo_servico));
    }

    private function jobNaFila(InterlabLotePostagem $lote): bool
    {
        if (config('queue.default') === 'sync' || ! Schema::hasTable('jobs')) {
            return false;
        }

        $classe = class_basename(ProcessarInterlabLotePostagemJob::class);
        $idMarca = 'i:'.$lote->id.';';

        return DB::table('jobs')
            ->orderBy('id')
            ->get(['id', 'payload'])
            ->contains(function (object $row) use ($classe, $idMarca, $lote): bool {
                $payload = (string) $row->payload;

                if (! str_contains($payload, $classe)) {
                    return false;
                }

                // Payload JSON escapa aspas do serialize PHP (id\";i:N;).
                return str_contains($payload, 'id";'.$idMarca)
                    || str_contains($payload, 'id\\";'.$idMarca)
                    || str_contains($payload, '"id":'.$lote->id);
            });
    }
}

<?php

namespace App\Actions\Interlab;

use App\Enums\InterlabLotePostagemItemStatus;
use App\Integrations\Correios\CorreiosLog;
use App\Jobs\Interlab\RastrearItemLotePostagemJob;
use App\Models\InterlabLotePostagemItem;
use Illuminate\Bus\UniqueLock;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EnfileirarRastreioItensLotePostagemAction
{
    public const INTERVALO_SEGUNDOS = 5;

    public function __construct(private CacheRepository $cache) {}

    /**
     * @return array{enfileirados: int, pulados: int}
     */
    public function execute(): array
    {
        $enfileirados = 0;
        $pulados = 0;
        $delay = 0;

        foreach ($this->itensCandidatos() as $item) {
            if ($this->jobNaFila($item)) {
                CorreiosLog::info('rastro.enfileirar.skip', [
                    'item_id' => $item->id,
                    'codigo_objeto' => $item->codigo_objeto,
                    'motivo' => 'job_na_fila',
                ]);
                $pulados++;

                continue;
            }

            $job = new RastrearItemLotePostagemJob($item);
            (new UniqueLock($this->cache))->release($job);
            RastrearItemLotePostagemJob::dispatch($item)
                ->delay(now()->addSeconds($delay));

            CorreiosLog::info('rastro.enfileirar.dispatch', [
                'item_id' => $item->id,
                'codigo_objeto' => $item->codigo_objeto,
                'delay_segundos' => $delay,
            ]);

            $enfileirados++;
            $delay += self::INTERVALO_SEGUNDOS;
        }

        return [
            'enfileirados' => $enfileirados,
            'pulados' => $pulados,
        ];
    }

    /**
     * @return list<InterlabLotePostagemItem>
     */
    private function itensCandidatos(): array
    {
        return InterlabLotePostagemItem::query()
            ->whereNotNull('codigo_objeto')
            ->where('codigo_objeto', '!=', '')
            ->where('status', '!=', InterlabLotePostagemItemStatus::Entregue->value)
            ->orderBy('id')
            ->get()
            ->all();
    }

    private function jobNaFila(InterlabLotePostagemItem $item): bool
    {
        if (config('queue.default') === 'sync' || ! Schema::hasTable('jobs')) {
            return false;
        }

        $classe = class_basename(RastrearItemLotePostagemJob::class);
        $idMarca = 'i:'.$item->id.';';

        return DB::table('jobs')
            ->orderBy('id')
            ->get(['id', 'payload'])
            ->contains(function (object $row) use ($classe, $idMarca, $item): bool {
                $payload = (string) $row->payload;

                if (! str_contains($payload, $classe)) {
                    return false;
                }

                return str_contains($payload, 'id";'.$idMarca)
                    || str_contains($payload, 'id\\";'.$idMarca)
                    || str_contains($payload, '"id":'.$item->id);
            });
    }
}

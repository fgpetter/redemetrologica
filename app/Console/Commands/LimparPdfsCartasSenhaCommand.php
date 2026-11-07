<?php

namespace App\Console\Commands;

use App\Models\DadosGeraDoc;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\UnableToRetrieveMetadata;
use RuntimeException;

class LimparPdfsCartasSenhaCommand extends Command
{
    protected $signature = 'interlab:limpar-pdfs-cartas-senha {--dry-run : Apenas simula a remoção}';

    protected $description = 'Remove PDFs de cartas-senha Interlab com mais de 15 dias';

    public function handle(): int
    {
        $disk = Storage::disk('public');
        $limite = now()->subDays(15)->getTimestamp();
        $removidos = 0;
        $falhas = 0;

        foreach ($disk->allFiles(DadosGeraDoc::DIRETORIO_CARTAS_SENHA) as $path) {
            if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'pdf') {
                continue;
            }

            try {
                if (! $disk->fileExists($path) || $disk->lastModified($path) >= $limite) {
                    continue;
                }

                if ($this->option('dry-run')) {
                    $this->line("Removeria {$path}.");
                    $removidos++;

                    continue;
                }

                $removeu = DB::transaction(function () use ($disk, $path, $limite): bool {
                    $candidatas = DadosGeraDoc::query()
                        ->whereIn('tipo', DadosGeraDoc::TIPOS_CARTA_SENHA)
                        ->whereIn('file_name', [$path, 'public/'.$path])
                        ->pluck('id');
                    $referencias = DadosGeraDoc::query()->whereKey($candidatas->all())
                        ->lockForUpdate()->get(['id', 'file_name']);

                    if (! $disk->fileExists($path) || $disk->lastModified($path) >= $limite) {
                        return false;
                    }

                    if (! $disk->delete($path)) {
                        throw new RuntimeException("Falha ao remover {$path}.");
                    }

                    $ids = $referencias
                        ->filter(fn (DadosGeraDoc $carta): bool => in_array($carta->getRawOriginal('file_name'), [$path, 'public/'.$path], true))
                        ->modelKeys();
                    if ($ids !== []) {
                        DadosGeraDoc::query()->whereKey($ids)->update(['file_name' => null]);
                    }

                    return true;
                });
                $removidos += (int) $removeu;
            } catch (\Throwable $exception) {
                if ($exception instanceof UnableToRetrieveMetadata && ! $disk->fileExists($path)) {
                    continue;
                }

                $this->error($exception->getMessage());
                $falhas++;
            }
        }

        $this->info(($this->option('dry-run') ? 'Simulação' : 'Concluído').". PDFs removidos: {$removidos}. Falhas: {$falhas}.");

        return $falhas ? self::FAILURE : self::SUCCESS;
    }
}

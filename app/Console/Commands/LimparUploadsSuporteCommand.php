<?php

namespace App\Console\Commands;

use App\Models\SuporteArquivo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class LimparUploadsSuporteCommand extends Command
{
    protected $signature = 'suporte:limpar-uploads';

    protected $description = 'Remove uploads temporários do suporte e do CKEditor abandonados há mais de 24 horas';

    public function handle(): int
    {
        $removidos = 0;
        SuporteArquivo::withTrashed()->whereNull('demanda_id')->where('created_at', '<', now()->subDay())
            ->chunkById(100, function ($arquivos) use (&$removidos): void {
                foreach ($arquivos as $arquivo) {
                    DB::transaction(function () use ($arquivo, &$removidos): void {
                        $atual = SuporteArquivo::withTrashed()->whereNull('demanda_id')
                            ->whereKey($arquivo->id)->lockForUpdate()->first();
                        if (! $atual) {
                            return;
                        }
                        $disk = Storage::disk('local');
                        if ($disk->exists($atual->caminho) && ! $disk->delete($atual->caminho)) {
                            throw new RuntimeException('Não foi possível remover um upload temporário do suporte.');
                        }
                        $atual->forceDelete();
                        $removidos++;
                    });
                }
            });
        $this->info("Uploads temporários removidos: {$removidos}.");
        $this->limparPastasTemporariasCkeditor();

        return self::SUCCESS;
    }

    private function limparPastasTemporariasCkeditor(): void
    {
        $limite = now()->subDay()->getTimestamp();
        $removidas = 0;

        foreach (File::directories(public_path()) as $pasta) {
            if (is_link($pasta) || ! preg_match('/^Temp[0-9]{9}$/', basename($pasta))) {
                continue;
            }
            if (File::lastModified($pasta) >= $limite) {
                continue;
            }
            foreach (File::allFiles($pasta, true) as $arquivo) {
                if ($arquivo->getMTime() >= $limite) {
                    continue 2;
                }
            }
            if (! File::deleteDirectory($pasta) || File::isDirectory($pasta)) {
                throw new RuntimeException('Não foi possível remover uma pasta temporária do CKEditor.');
            }
            $removidas++;
        }

        $this->info("Pastas temporárias do CKEditor removidas: {$removidas}.");
    }
}

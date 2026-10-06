<?php

namespace App\Actions\Interlab;

use App\Enums\InterlabLotePostagemStatus;
use App\Models\InterlabLotePostagem;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class CompactarDocumentosLotePostagemAction
{
    public function execute(InterlabLotePostagem $lote): BinaryFileResponse
    {
        $lote->loadMissing('etiquetas');

        if ($lote->status !== InterlabLotePostagemStatus::Concluido) {
            abort(404);
        }

        $paths = $this->pathsDosDocumentos($lote);

        if ($paths === []) {
            abort(404);
        }

        $disk = Storage::disk('local');
        $zipPath = tempnam(sys_get_temp_dir(), 'lote-').'.zip';
        $zip = new ZipArchive;

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Não foi possível criar o arquivo ZIP do lote.');
        }

        foreach ($paths as $nome => $path) {
            $zip->addFile($disk->path($path), $nome);
        }

        $zip->close();

        return response()
            ->download($zipPath, 'lote-'.$lote->uid.'.zip', [
                'Content-Type' => 'application/zip',
            ])
            ->deleteFileAfterSend(true);
    }

    /**
     * @return array<string, string>
     */
    private function pathsDosDocumentos(InterlabLotePostagem $lote): array
    {
        $disk = Storage::disk('local');
        $paths = [];

        foreach ($lote->etiquetas as $indice => $etiqueta) {
            if (blank($etiqueta->etiqueta_path) || ! $disk->exists($etiqueta->etiqueta_path)) {
                abort(404);
            }

            $paths['etiqueta-'.($indice + 1).'.pdf'] = $etiqueta->etiqueta_path;
        }

        if (filled($lote->dace_pdf_path) && $disk->exists($lote->dace_pdf_path)) {
            $paths['dace.pdf'] = $lote->dace_pdf_path;

            return $paths;
        }

        if (filled($lote->declaracao_conteudo_pdf_path) && $disk->exists($lote->declaracao_conteudo_pdf_path)) {
            $paths['declaracao-conteudo.pdf'] = $lote->declaracao_conteudo_pdf_path;

            return $paths;
        }

        abort(404);
    }
}

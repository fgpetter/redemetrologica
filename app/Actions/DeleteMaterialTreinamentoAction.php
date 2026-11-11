<?php

namespace App\Actions;

use App\Models\AgendaCursoArquivo;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class DeleteMaterialTreinamentoAction
{
    public function execute(AgendaCursoArquivo $material): void
    {
        $disk = Storage::disk('public');
        if ($disk->exists($material->arquivo) && ! $disk->delete($material->arquivo)) {
            throw new RuntimeException('Falha ao excluir arquivo de material de treinamento.');
        }

        $material->delete();
    }
}

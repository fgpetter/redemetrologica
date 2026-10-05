<?php

namespace App\Http\Controllers\Interlab;

use App\Actions\Interlab\CompactarDocumentosLotePostagemAction;
use App\Http\Controllers\Controller;
use App\Models\InterlabLotePostagem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DownloadDocumentosLotePostagemController extends Controller
{
    public function __invoke(
        InterlabLotePostagem $lotePostagem,
        CompactarDocumentosLotePostagemAction $action,
    ): BinaryFileResponse {
        return $action->execute($lotePostagem);
    }
}

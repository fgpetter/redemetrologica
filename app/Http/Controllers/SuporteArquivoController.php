<?php

namespace App\Http\Controllers;

use App\Actions\Suporte\ArquivosSuporteAction;
use App\Http\Requests\UploadImagemSuporteRequest;
use App\Models\SuporteArquivo;
use App\Models\SuporteDemanda;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SuporteArquivoController extends Controller
{
    public function upload(UploadImagemSuporteRequest $request, string $rascunho, ArquivosSuporteAction $arquivos): JsonResponse
    {
        $arquivo = $arquivos->enviar($request->user(), $request->file('upload'), $rascunho, true);

        return response()->json([
            'uploaded' => 1,
            'fileName' => $arquivo->nome,
            'url' => route('suporte-arquivo-exibir', $arquivo->uid),
        ]);
    }

    public function exibir(SuporteArquivo $arquivo): BinaryFileResponse
    {
        $this->autorizar($arquivo);
        abort_unless(str_starts_with($arquivo->mime, 'image/'), 404);

        return response()->file(Storage::disk('local')->path($arquivo->caminho), [
            'Content-Type' => $arquivo->mime,
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function download(SuporteArquivo $arquivo): StreamedResponse
    {
        $this->autorizar($arquivo);

        return Storage::disk('local')->download($arquivo->caminho, $arquivo->nome, [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function autorizar(SuporteArquivo $arquivo): void
    {
        $user = auth()->user();
        SuporteDemanda::autorizarAcesso($user);
        if ($arquivo->rascunho) {
            abort_unless($arquivo->user_id === $user->id, 404);
            SuporteArquivo::autorizarRascunho($arquivo->rascunho, $user);
        } else {
            abort_unless($arquivo->demanda()->exists(), 404);
            if ($arquivo->atividade_id) {
                $atividade = $arquivo->atividade;
                abort_unless($atividade, 404);
                abort_if($atividade->parent_id && ! $atividade->demanda->atividades()->whereKey($atividade->parent_id)->exists(), 404);
            }
        }
        abort_unless(Storage::disk('local')->exists($arquivo->caminho), 404);
    }
}

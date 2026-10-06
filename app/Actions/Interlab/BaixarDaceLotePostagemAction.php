<?php

namespace App\Actions\Interlab;

use App\Enums\CorreiosPrePostagemStatus;
use App\Enums\InterlabLotePostagemEtiquetaStatus;
use App\Enums\InterlabLotePostagemItemStatus;
use App\Enums\InterlabLotePostagemStatus;
use App\Exceptions\CorreiosApiException;
use App\Integrations\Correios\CorreiosLog;
use App\Integrations\Correios\CorreiosPrePostagemClient;
use App\Mail\InterlabLotePostagemDocumentosMail;
use App\Models\InterlabLotePostagem;
use App\Models\InterlabLotePostagemItem;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class BaixarDaceLotePostagemAction
{
    public function __construct(
        private CorreiosPrePostagemClient $prePostagem,
        private EncerrarInterlabLotePostagemComErroAction $encerrar,
    ) {}

    public function execute(InterlabLotePostagem $lote): ResultadoEtapaLotePostagem
    {
        $lote->refresh();

        CorreiosLog::info('dace.inicio', [
            'lote_id' => $lote->id,
            'lote_uid' => $lote->uid,
            'dace_pdf_path' => $lote->dace_pdf_path,
        ]);

        if (! $this->precondicaoAtendida($lote)) {
            $this->encerrar->execute(
                $lote,
                InterlabLotePostagemStatus::GerandoDeclaracao->value,
                'Pré-condição da DACE não atendida (pré-postagens e etiquetas).',
            );

            return ResultadoEtapaLotePostagem::falhou();
        }

        $pdfPath = 'correios/dace/'.$lote->uid.'.pdf';
        $disk = Storage::disk('local');
        $pdfExiste = filled($lote->dace_pdf_path) && $disk->exists($lote->dace_pdf_path);

        if ($pdfExiste) {
            CorreiosLog::info('dace.skip', [
                'lote_id' => $lote->id,
                'motivo' => 'pdf',
                'dace_pdf_path' => $lote->dace_pdf_path,
            ]);

            if ($lote->status !== InterlabLotePostagemStatus::Concluido) {
                $lote->update([
                    'status' => InterlabLotePostagemStatus::Concluido,
                    'ultima_progressao_em' => now(),
                ]);
            }

            $this->enfileirarDocumentosSeNecessario($lote->fresh());

            return ResultadoEtapaLotePostagem::concluida();
        }

        $resultado = $this->baixarDace($lote, $pdfPath);

        if (! $resultado->estaConcluida()) {
            return $resultado;
        }

        $lote->update([
            'dace_pdf_path' => $pdfPath,
            'status' => InterlabLotePostagemStatus::Concluido,
            'ultima_progressao_em' => now(),
            'etapa_erro' => null,
            'erro_mensagem' => null,
        ]);

        CorreiosLog::info('dace.pdf_ok', [
            'lote_id' => $lote->id,
            'dace_pdf_path' => $pdfPath,
            'status_persistido' => InterlabLotePostagemStatus::Concluido->value,
        ]);

        $this->enfileirarDocumentosSeNecessario($lote->fresh());

        return ResultadoEtapaLotePostagem::concluida();
    }

    private function enfileirarDocumentosSeNecessario(InterlabLotePostagem $lote): void
    {
        if ($lote->documentos_enviados_em !== null) {
            return;
        }

        /** @var list<string> $destinatarios */
        $destinatarios = config('services.correios.documentos_email', []);

        if ($destinatarios === []) {
            return;
        }

        Mail::to($destinatarios)->queue(new InterlabLotePostagemDocumentosMail($lote));

        $lote->update(['documentos_enviados_em' => now()]);

        CorreiosLog::info('dace.email_enfileirado', [
            'lote_id' => $lote->id,
            'lote_uid' => $lote->uid,
            'destinatarios' => $destinatarios,
        ]);
    }

    private function precondicaoAtendida(InterlabLotePostagem $lote): bool
    {
        $itens = $lote->itens()->get();

        if ($itens->isEmpty()) {
            return false;
        }

        $itensOk = $itens->every(fn (InterlabLotePostagemItem $item): bool => filled($item->id_prepostagem)
            && in_array($item->status, [
                InterlabLotePostagemItemStatus::Prepostado,
                InterlabLotePostagemItemStatus::EtiquetaGerada,
            ], true)
            && $item->status_atual === CorreiosPrePostagemStatus::Prepostado);

        if (! $itensOk) {
            return false;
        }

        $etiquetas = $lote->etiquetas()->get();

        return $etiquetas->isNotEmpty()
            && $etiquetas->every(fn ($etiqueta): bool => $etiqueta->status === InterlabLotePostagemEtiquetaStatus::Gerada);
    }

    private function baixarDace(InterlabLotePostagem $lote, string $pdfPath): ResultadoEtapaLotePostagem
    {
        $ids = $lote->itens()->orderBy('id')->pluck('id_prepostagem')->filter()->values()->all();

        try {
            $response = $this->prePostagem->imprimirDace($ids);
        } catch (ConnectionException $exception) {
            CorreiosLog::exception('dace.timeout', $exception, [
                'lote_id' => $lote->id,
            ]);

            return ResultadoEtapaLotePostagem::retentar(1);
        }

        return $this->interpretarDace($lote, $response, $pdfPath);
    }

    private function interpretarDace(
        InterlabLotePostagem $lote,
        Response $response,
        string $pdfPath,
    ): ResultadoEtapaLotePostagem {
        $status = $response->status();
        $json = $response->json();

        if ($status === 429 || $status >= 500) {
            CorreiosLog::warning('dace.transitorio', [
                'lote_id' => $lote->id,
                'http' => $status,
            ]);

            return ResultadoEtapaLotePostagem::retentar($this->atraso($response, 1));
        }

        if ($status >= 400) {
            CorreiosLog::error('dace.http_definitivo', [
                'lote_id' => $lote->id,
                'http' => $status,
            ]);

            $this->encerrar->execute(
                $lote,
                InterlabLotePostagemStatus::GerandoDeclaracao->value,
                CorreiosApiException::fromResponse($response, CorreiosPrePostagemClient::PATH_DACE)->getMessage(),
            );

            return ResultadoEtapaLotePostagem::falhou();
        }

        $dados = is_array($json) ? ($json['dados'] ?? null) : null;

        if (! is_string($dados) || $dados === '') {
            CorreiosLog::error('dace.corpo_invalido', [
                'lote_id' => $lote->id,
                'http' => $status,
                'chaves' => is_array($json) ? array_keys($json) : [],
            ]);

            $this->encerrar->execute(
                $lote,
                InterlabLotePostagemStatus::GerandoDeclaracao->value,
                'A impressão da DACE não retornou PDF utilizável.',
            );

            return ResultadoEtapaLotePostagem::falhou();
        }

        $pdf = base64_decode($dados, true);

        if ($pdf === false || ! str_starts_with($pdf, '%PDF')) {
            CorreiosLog::error('dace.corpo_invalido', [
                'lote_id' => $lote->id,
                'http' => $status,
                'dados_bytes' => is_string($pdf) ? strlen($pdf) : 0,
            ]);

            $this->encerrar->execute(
                $lote,
                InterlabLotePostagemStatus::GerandoDeclaracao->value,
                'A impressão da DACE não retornou PDF utilizável.',
            );

            return ResultadoEtapaLotePostagem::falhou();
        }

        if (! Storage::disk('local')->exists($pdfPath)) {
            Storage::disk('local')->put($pdfPath, $pdf);
        }

        CorreiosLog::info('dace.download_ok', [
            'lote_id' => $lote->id,
            'dace_pdf_path' => $pdfPath,
            'pdf_bytes' => strlen($pdf),
        ]);

        return ResultadoEtapaLotePostagem::concluida();
    }

    private function atraso(Response $response, int $tentativa): int
    {
        $backoff = (int) (2 ** max(0, $tentativa - 1));
        $retryAfter = (int) $response->header('Retry-After');

        return max($backoff, $retryAfter, 1);
    }
}

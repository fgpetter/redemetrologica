<?php

namespace App\Actions\Interlab;

use App\Enums\CorreiosPrePostagemStatus;
use App\Enums\InterlabLotePostagemEtiquetaStatus;
use App\Enums\InterlabLotePostagemItemStatus;
use App\Enums\InterlabLotePostagemStatus;
use App\Exceptions\CorreiosApiException;
use App\Integrations\Correios\CorreiosLog;
use App\Integrations\Correios\CorreiosPrePostagemClient;
use App\Models\InterlabLotePostagem;
use App\Models\InterlabLotePostagemEtiqueta;
use App\Models\InterlabLotePostagemItem;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Sleep;

class EmitirEtiquetasLotePostagemAction
{
    private const POLL_TENTATIVAS = 5;

    private const POLL_INTERVALO_SEGUNDOS = 2;

    public function __construct(
        private CorreiosPrePostagemClient $prePostagem,
        private EncerrarInterlabLotePostagemComErroAction $encerrar,
    ) {}

    public function execute(InterlabLotePostagem $lote, int $tentativa = 1): ResultadoEtapaLotePostagem
    {
        $lote->refresh();

        CorreiosLog::info('etiqueta.inicio', [
            'lote_id' => $lote->id,
            'lote_uid' => $lote->uid,
            'tentativa' => $tentativa,
        ]);

        if (! $this->precondicaoAtendida($lote)) {
            $this->encerrar->execute(
                $lote,
                InterlabLotePostagemStatus::GerandoEtiquetas->value,
                'Nem todos os itens estão pré-postados para emitir etiquetas.',
            );

            return ResultadoEtapaLotePostagem::falhou();
        }

        $this->garantirGrupos($lote);
        $lote->load(['etiquetas.itens']);

        foreach ($lote->etiquetas()->orderBy('id')->get() as $etiqueta) {
            $resultado = $this->processarGrupo($lote, $etiqueta, $tentativa);

            if ($resultado->estaFalhou() || $resultado->deveRetentar()) {
                return $resultado;
            }
        }

        return ResultadoEtapaLotePostagem::concluida();
    }

    private function precondicaoAtendida(InterlabLotePostagem $lote): bool
    {
        $itens = $lote->itens()->get();

        if ($itens->isEmpty()) {
            return false;
        }

        return $itens->every(fn (InterlabLotePostagemItem $item): bool => in_array($item->status, [
            InterlabLotePostagemItemStatus::Prepostado,
            InterlabLotePostagemItemStatus::EtiquetaGerada,
        ], true)
            && filled($item->id_prepostagem)
            && $item->status_atual === CorreiosPrePostagemStatus::Prepostado);
    }

    private function garantirGrupos(InterlabLotePostagem $lote): void
    {
        if ($lote->etiquetas()->exists()) {
            return;
        }

        $itens = $lote->itens()->orderBy('id')->get();
        $grupos = $itens->chunk(CorreiosPrePostagemClient::TAMANHO_GRUPO_ETIQUETA);
        $criados = [];

        DB::transaction(function () use ($lote, $grupos, &$criados): void {
            foreach ($grupos as $grupo) {
                $etiqueta = $lote->etiquetas()->create([
                    'status' => InterlabLotePostagemEtiquetaStatus::Pendente,
                ]);

                InterlabLotePostagemItem::query()
                    ->whereIn('id', $grupo->pluck('id'))
                    ->update(['interlab_lote_postagem_etiqueta_id' => $etiqueta->id]);

                $criados[] = [
                    'etiqueta_id' => $etiqueta->id,
                    'tamanho' => $grupo->count(),
                    'item_ids' => $grupo->pluck('id')->all(),
                ];
            }
        });

        CorreiosLog::info('etiqueta.grupos_criados', [
            'lote_id' => $lote->id,
            'grupos' => $criados,
        ]);
    }

    private function processarGrupo(
        InterlabLotePostagem $lote,
        InterlabLotePostagemEtiqueta $etiqueta,
        int $tentativa,
    ): ResultadoEtapaLotePostagem {
        $etiqueta->refresh();

        if ($etiqueta->status === InterlabLotePostagemEtiquetaStatus::Gerada && filled($etiqueta->etiqueta_path)) {
            CorreiosLog::info('etiqueta.skip', [
                'lote_id' => $lote->id,
                'etiqueta_id' => $etiqueta->id,
                'motivo' => 'path',
                'etiqueta_path' => $etiqueta->etiqueta_path,
            ]);

            return ResultadoEtapaLotePostagem::concluida();
        }

        if ($etiqueta->status === InterlabLotePostagemEtiquetaStatus::Solicitada && blank($etiqueta->id_recibo_rotulo)) {
            CorreiosLog::error('etiqueta.sem_recibo', [
                'lote_id' => $lote->id,
                'etiqueta_id' => $etiqueta->id,
            ]);

            $etiqueta->update([
                'status' => InterlabLotePostagemEtiquetaStatus::Erro,
                'etapa_erro' => InterlabLotePostagemStatus::GerandoEtiquetas->value,
                'erro_mensagem' => 'Etiqueta solicitada sem recibo persistido.',
            ]);

            $this->encerrar->execute(
                $lote,
                InterlabLotePostagemStatus::GerandoEtiquetas->value,
                'Grupo de etiquetas solicitada sem recibo; não foi reenviado automaticamente.',
            );

            return ResultadoEtapaLotePostagem::falhou();
        }

        if (blank($etiqueta->id_recibo_rotulo)) {
            $resultado = $this->solicitarRotulo($lote, $etiqueta);

            if (! $resultado->estaConcluida()) {
                return $resultado;
            }

            $etiqueta->refresh();
        } else {
            CorreiosLog::info('etiqueta.skip', [
                'lote_id' => $lote->id,
                'etiqueta_id' => $etiqueta->id,
                'motivo' => 'recibo',
                'id_recibo_rotulo' => $etiqueta->id_recibo_rotulo,
            ]);
        }

        return $this->baixarPdf($lote, $etiqueta, $tentativa);
    }

    private function solicitarRotulo(
        InterlabLotePostagem $lote,
        InterlabLotePostagemEtiqueta $etiqueta,
    ): ResultadoEtapaLotePostagem {
        $reivindicada = InterlabLotePostagemEtiqueta::query()
            ->whereKey($etiqueta->id)
            ->where('status', InterlabLotePostagemEtiquetaStatus::Pendente->value)
            ->update([
                'status' => InterlabLotePostagemEtiquetaStatus::Solicitada->value,
                'solicitacao_iniciada_em' => now(),
            ]);

        if ($reivindicada === 0) {
            $etiqueta->refresh();

            return $this->processarGrupo($lote, $etiqueta, 1);
        }

        CorreiosLog::info('etiqueta.reivindicada', [
            'lote_id' => $lote->id,
            'etiqueta_id' => $etiqueta->id,
        ]);

        $ids = $etiqueta->itens()->orderBy('id')->pluck('id_prepostagem')->filter()->values()->all();

        try {
            $response = $this->prePostagem->solicitarRotulo($ids);
        } catch (ConnectionException $exception) {
            CorreiosLog::exception('etiqueta.timeout_solicitar', $exception, [
                'lote_id' => $lote->id,
                'etiqueta_id' => $etiqueta->id,
            ]);

            return ResultadoEtapaLotePostagem::retentar(1);
        }

        return $this->interpretarSolicitacao($lote, $etiqueta, $response);
    }

    private function interpretarSolicitacao(
        InterlabLotePostagem $lote,
        InterlabLotePostagemEtiqueta $etiqueta,
        Response $response,
    ): ResultadoEtapaLotePostagem {
        $status = $response->status();
        $json = $response->json();
        $chaves = is_array($json) ? array_keys($json) : [];
        $idRecibo = is_array($json)
            ? ($json['idRecibo'] ?? $json['recibo'] ?? $json['id'] ?? null)
            : null;

        if ($status === 429 || $status >= 500) {
            CorreiosLog::warning('etiqueta.transitorio', [
                'lote_id' => $lote->id,
                'etiqueta_id' => $etiqueta->id,
                'http' => $status,
                'chaves' => $chaves,
            ]);

            return ResultadoEtapaLotePostagem::retentar($this->atraso($response, 1));
        }

        if ($status >= 400 || blank($idRecibo)) {
            CorreiosLog::error('etiqueta.http_definitivo', [
                'lote_id' => $lote->id,
                'etiqueta_id' => $etiqueta->id,
                'http' => $status,
                'chaves' => $chaves,
                'idRecibo' => $idRecibo,
            ]);

            $mensagem = $status >= 400
                ? CorreiosApiException::fromResponse($response, CorreiosPrePostagemClient::PATH_ROTULO)->getMessage()
                : 'Resposta de rótulo sem idRecibo.';

            $etiqueta->update([
                'status' => InterlabLotePostagemEtiquetaStatus::Erro,
                'etapa_erro' => InterlabLotePostagemStatus::GerandoEtiquetas->value,
                'erro_mensagem' => $mensagem,
            ]);

            $this->encerrar->execute(
                $lote,
                InterlabLotePostagemStatus::GerandoEtiquetas->value,
                $mensagem,
            );

            return ResultadoEtapaLotePostagem::falhou();
        }

        $etiqueta->update(['id_recibo_rotulo' => (string) $idRecibo]);

        CorreiosLog::info('etiqueta.recibo', [
            'lote_id' => $lote->id,
            'etiqueta_id' => $etiqueta->id,
            'id_recibo_rotulo' => $idRecibo,
            'chaves' => $chaves,
        ]);

        return ResultadoEtapaLotePostagem::concluida();
    }

    private function baixarPdf(
        InterlabLotePostagem $lote,
        InterlabLotePostagemEtiqueta $etiqueta,
        int $tentativa,
    ): ResultadoEtapaLotePostagem {
        $path = 'correios/etiquetas/'.$etiqueta->uid.'.pdf';

        if (Storage::disk('local')->exists($path)) {
            return $this->marcarGerada($lote, $etiqueta, $path, Storage::disk('local')->size($path));
        }

        for ($poll = 1; $poll <= self::POLL_TENTATIVAS; $poll++) {
            try {
                $response = $this->prePostagem->downloadRotulo((string) $etiqueta->id_recibo_rotulo);
            } catch (ConnectionException $exception) {
                CorreiosLog::exception('etiqueta.timeout_download', $exception, [
                    'lote_id' => $lote->id,
                    'etiqueta_id' => $etiqueta->id,
                    'poll' => $poll,
                ]);

                return ResultadoEtapaLotePostagem::retentar(1);
            }

            $status = $response->status();
            $json = $response->json();
            $dados = is_array($json) ? ($json['dados'] ?? null) : null;
            $dadosPresente = is_string($dados) && $dados !== '';

            CorreiosLog::info('etiqueta.poll', [
                'lote_id' => $lote->id,
                'etiqueta_id' => $etiqueta->id,
                'tentativa' => $poll,
                'http' => $status,
                'dados_presente' => $dadosPresente,
                'chaves' => is_array($json) ? array_keys($json) : [],
            ]);

            if ($status === 429 || $status >= 500) {
                return ResultadoEtapaLotePostagem::retentar($this->atraso($response, $tentativa));
            }

            if ($status >= 400) {
                $mensagem = CorreiosApiException::fromResponse($response, CorreiosPrePostagemClient::PATH_DOWNLOAD)->getMessage();
                $etiqueta->update([
                    'status' => InterlabLotePostagemEtiquetaStatus::Erro,
                    'etapa_erro' => InterlabLotePostagemStatus::GerandoEtiquetas->value,
                    'erro_mensagem' => $mensagem,
                ]);
                $this->encerrar->execute(
                    $lote,
                    InterlabLotePostagemStatus::GerandoEtiquetas->value,
                    $mensagem,
                );

                return ResultadoEtapaLotePostagem::falhou();
            }

            if ($dadosPresente) {
                $binario = base64_decode($dados, true);

                if ($binario === false || $binario === '') {
                    $this->encerrar->execute(
                        $lote,
                        InterlabLotePostagemStatus::GerandoEtiquetas->value,
                        'PDF de etiqueta inválido no campo dados.',
                    );

                    return ResultadoEtapaLotePostagem::falhou();
                }

                if (! Storage::disk('local')->exists($path)) {
                    Storage::disk('local')->put($path, $binario);
                }

                return $this->marcarGerada($lote, $etiqueta, $path, strlen($binario));
            }

            if ($poll < self::POLL_TENTATIVAS) {
                Sleep::for(self::POLL_INTERVALO_SEGUNDOS)->seconds();
            }
        }

        CorreiosLog::warning('etiqueta.poll_esgotado', [
            'lote_id' => $lote->id,
            'etiqueta_id' => $etiqueta->id,
            'id_recibo_rotulo' => $etiqueta->id_recibo_rotulo,
        ]);

        return ResultadoEtapaLotePostagem::retentar(max(2, $tentativa));
    }

    private function marcarGerada(
        InterlabLotePostagem $lote,
        InterlabLotePostagemEtiqueta $etiqueta,
        string $path,
        int $bytes,
    ): ResultadoEtapaLotePostagem {
        $etiqueta->update([
            'etiqueta_path' => $path,
            'status' => InterlabLotePostagemEtiquetaStatus::Gerada,
            'erro_mensagem' => null,
            'etapa_erro' => null,
        ]);

        $etiqueta->itens()->update([
            'status' => InterlabLotePostagemItemStatus::EtiquetaGerada->value,
        ]);

        $lote->update(['ultima_progressao_em' => now()]);

        CorreiosLog::info('etiqueta.gerada', [
            'lote_id' => $lote->id,
            'etiqueta_id' => $etiqueta->id,
            'etiqueta_path' => $path,
            'bytes' => $bytes,
            'status_persistido' => InterlabLotePostagemEtiquetaStatus::Gerada->value,
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

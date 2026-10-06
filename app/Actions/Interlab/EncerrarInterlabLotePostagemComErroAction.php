<?php

namespace App\Actions\Interlab;

use App\Enums\InterlabLotePostagemEtiquetaStatus;
use App\Enums\InterlabLotePostagemItemStatus;
use App\Enums\InterlabLotePostagemStatus;
use App\Integrations\Correios\CorreiosLog;
use App\Mail\InterlabLotePostagemErroMail;
use App\Models\InterlabLotePostagem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class EncerrarInterlabLotePostagemComErroAction
{
    public function execute(InterlabLotePostagem $lote, string $etapa, string $mensagem): InterlabLotePostagem
    {
        $lote->refresh();

        if ($lote->status === InterlabLotePostagemStatus::Erro) {
            return $lote;
        }

        $mensagem = mb_substr($mensagem, 0, 2000);

        CorreiosLog::error('lote.encerrado', [
            'lote_id' => $lote->id,
            'lote_uid' => $lote->uid,
            'nome' => $lote->nome,
            'etapa' => $etapa,
            'mensagem' => $mensagem,
        ]);

        DB::transaction(function () use ($lote, $etapa, $mensagem): void {
            $lote->update([
                'status' => InterlabLotePostagemStatus::Erro,
                'etapa_erro' => $etapa,
                'erro_mensagem' => $mensagem,
            ]);

            $statusPendentes = match ($etapa) {
                InterlabLotePostagemStatus::ClassificandoServico->value => [
                    InterlabLotePostagemItemStatus::AguardandoClassificacao->value,
                ],
                InterlabLotePostagemStatus::GerandoPrePostagens->value => [
                    InterlabLotePostagemItemStatus::Classificado->value,
                ],
                InterlabLotePostagemStatus::GerandoEtiquetas->value => [
                    InterlabLotePostagemItemStatus::Prepostado->value,
                ],
                InterlabLotePostagemStatus::GerandoDeclaracao->value => [
                    InterlabLotePostagemItemStatus::Prepostado->value,
                    InterlabLotePostagemItemStatus::EtiquetaGerada->value,
                ],
                default => [
                    InterlabLotePostagemItemStatus::AguardandoClassificacao->value,
                    InterlabLotePostagemItemStatus::Classificado->value,
                    InterlabLotePostagemItemStatus::Prepostado->value,
                ],
            };

            $lote->itens()
                ->whereIn('status', $statusPendentes)
                ->update([
                    'status' => InterlabLotePostagemItemStatus::Erro->value,
                    'erro_mensagem' => $mensagem,
                ]);

            if ($etapa === InterlabLotePostagemStatus::GerandoEtiquetas->value) {
                $lote->etiquetas()
                    ->where('status', '!=', InterlabLotePostagemEtiquetaStatus::Gerada->value)
                    ->update([
                        'status' => InterlabLotePostagemEtiquetaStatus::Erro->value,
                        'etapa_erro' => $etapa,
                        'erro_mensagem' => $mensagem,
                    ]);
            }
        });

        Mail::to((string) config('services.correios.admin_email'))
            ->queue(new InterlabLotePostagemErroMail($lote->fresh()));

        return $lote->fresh();
    }
}

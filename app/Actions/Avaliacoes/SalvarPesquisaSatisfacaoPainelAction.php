<?php

namespace App\Actions\Avaliacoes;

use App\Models\AgendaAvaliacao;
use Illuminate\Support\Facades\DB;

class SalvarPesquisaSatisfacaoPainelAction
{
    /**
     * Atualiza a pesquisa de satisfação pelo painel e recalcula a média.
     *
     * @param  array<string, mixed>  $dados
     */
    public function execute(AgendaAvaliacao $avaliacao, array $dados): void
    {
        DB::transaction(function () use ($avaliacao, $dados) {
            $pesquisa = $avaliacao->pesquisa;

            if ($pesquisa === null) {
                return;
            }

            $avaliacao->loadMissing('areas.avaliador.pessoa');

            $comentarios = [];
            foreach ($dados['comentarios'] ?? [] as $avaliadorId => $comentario) {
                $area = $avaliacao->areas->firstWhere('avaliador_id', (int) $avaliadorId);
                $comentarios[] = [
                    'avaliador_id' => (int) $avaliadorId,
                    'nome' => $area?->avaliador?->pessoa?->nome_razao,
                    'comentario' => $comentario,
                ];
            }

            $payload = [
                'criterios_harmoniosos' => $dados['criterios_harmoniosos'] ?? null,
                'divergencias' => $dados['divergencias'] ?? null,
                'pontos_melhoria' => $dados['pontos_melhoria'] ?? null,
                'comentarios_avaliadores' => $comentarios,
                'responsavel' => $dados['responsavel'] ?? null,
                'conferida' => (bool) (int) ($dados['conferida'] ?? 0),
            ];

            $notas = [];
            $completas = true;
            for ($i = 1; $i <= 7; $i++) {
                $valor = $dados["nota_{$i}"] ?? null;
                $payload["nota_{$i}"] = $valor;
                if ($valor === null || $valor === '') {
                    $completas = false;
                } else {
                    $notas[] = (int) $valor;
                }
            }

            if ($completas && $pesquisa->preenchido_em === null) {
                $payload['preenchido_em'] = now();
            }

            $pesquisa->update($payload);

            if ($completas) {
                $avaliacao->update([
                    'med_pesquisa' => round(array_sum($notas) / 7, 2),
                ]);
            }
        });
    }
}

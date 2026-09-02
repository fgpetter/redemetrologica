<?php

namespace App\Actions\Avaliacoes;

use App\Models\AgendaAvaliacao;
use Illuminate\Support\Facades\DB;

class ResponderPesquisaSatisfacaoAction
{
    /**
     * Grava a resposta pública da pesquisa de satisfação.
     *
     * @param  array<string, mixed>  $dados
     */
    public function execute(AgendaAvaliacao $avaliacao, array $dados): void
    {
        DB::transaction(function () use ($avaliacao, $dados) {
            $pesquisa = $avaliacao->pesquisa;

            if ($pesquisa === null || $pesquisa->preenchido_em !== null) {
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

            $notas = [];
            for ($i = 1; $i <= 7; $i++) {
                $notas[] = (int) $dados["nota_{$i}"];
            }

            $pesquisa->update([
                'nota_1' => $dados['nota_1'],
                'nota_2' => $dados['nota_2'],
                'nota_3' => $dados['nota_3'],
                'nota_4' => $dados['nota_4'],
                'nota_5' => $dados['nota_5'],
                'nota_6' => $dados['nota_6'],
                'nota_7' => $dados['nota_7'],
                'criterios_harmoniosos' => $dados['criterios_harmoniosos'],
                'divergencias' => $dados['divergencias'],
                'pontos_melhoria' => $dados['pontos_melhoria'],
                'comentarios_avaliadores' => $comentarios,
                'responsavel' => $dados['responsavel'],
                'preenchido_em' => now(),
            ]);

            $avaliacao->update([
                'med_pesquisa' => round(array_sum($notas) / 7, 2),
            ]);
        });
    }
}

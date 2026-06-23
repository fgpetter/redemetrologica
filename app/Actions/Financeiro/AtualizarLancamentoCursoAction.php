<?php

namespace App\Actions\Financeiro;

use App\Models\CursoInscrito;
use App\Models\LancamentoFinanceiro;
use Illuminate\Support\Carbon;

class AtualizarLancamentoCursoAction
{
    /**
     * Atualiza ou remove lançamento financeiro após alteração/cancelamento de inscrito.
     */
    public function execute(CursoInscrito $inscrito): void
    {
        // verifica se o lancamento está atrelado a uma empresa
        $lancamento_pj = LancamentoFinanceiro::where('pessoa_id', $inscrito->empresa_id)
            ->where('agenda_curso_id', $inscrito->agenda_curso_id)
            ->first();

        // se sim atualiza o lançamento financeiro da empresa e recalcula o total do valor
        if ($lancamento_pj) {
            $dados_empresa = CursoInscrito::where('empresa_id', $inscrito->empresa_id)
                ->where('agenda_curso_id', $inscrito->agenda_curso_id)
                ->with('pessoa')
                ->get();

            if ($dados_empresa->isEmpty()) {
                $lancamento_pj->delete();

                return;
            }

            $observacoes = '';
            foreach ($dados_empresa as $dado) {
                $data = Carbon::parse($dado->data_inscricao)->format('d/m/Y H:i');
                $observacoes .= linhaObservacaoInscricao($dado->nome, $dado->valor, $data);
            }

            $lancamento_pj->update([
                'valor' => $dados_empresa->sum('valor'),
                'observacoes' => $observacoes,
            ]);
        } else { // se não, atualiza o lancamento da pessoa fisica

            LancamentoFinanceiro::where('pessoa_id', $inscrito->pessoa_id)
                ->where('agenda_curso_id', $inscrito->agenda_curso_id)
                ->update([
                    'valor' => formataMoeda($inscrito->valor),
                    'observacoes' => linhaObservacaoInscricao($inscrito->nome, $inscrito->valor),
                ]);

        }
    }
}

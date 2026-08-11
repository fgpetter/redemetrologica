<?php

namespace App\Actions\Avaliacoes;

use App\Models\AgendaAvaliacao;

class SalvarOrcamentoAvaliacaoAction
{
    /**
     * Persiste somente os dados informados do orçamento.
     *
     * @param  array{
     *     perc_lucro?: mixed,
     *     data_envio_proposta?: ?string,
     *     observacoes_orcamento?: ?string
     * }  $dados
     */
    public function execute(AgendaAvaliacao $avaliacao, array $dados): AgendaAvaliacao
    {
        $avaliacao->update(array_intersect_key(
            $dados,
            array_flip(['perc_lucro', 'data_envio_proposta', 'observacoes_orcamento'])
        ));

        return $avaliacao->fresh();
    }
}

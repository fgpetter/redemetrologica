<?php

namespace App\Actions\Financeiro;

use App\Models\CentroCusto;
use App\Models\InterlabInscrito;
use App\Models\LancamentoFinanceiro;
use App\Models\PlanoConta;

class GerarLancamentoInterlabAction
{
    /**
     * Gera ou atualiza um lançamento financeiro para uma inscrição do Interlab
     *
     * @param  float|null  $valor
     */
    public function execute(InterlabInscrito $inscrito, $valor = null): LancamentoFinanceiro
    {
        // Garante que os relacionamentos necessários estejam carregados
        $inscrito->loadMissing(['agendaInterlab.interlab', 'empresa', 'laboratorio', 'lancamentoFinanceiro']);

        // Se o valor for nulo, usa o valor da inscrição
        if (is_null($valor)) {
            $valor = $inscrito->valor;
        }

        $agenda_interlab = $inscrito->agendaInterlab;
        $empresa = $inscrito->empresa;
        $laboratorio = $inscrito->laboratorio;

        $historico = 'Inscrição no interlab - '.($agenda_interlab->interlab->nome ?? 'N/A');
        $laboratorioNome = $laboratorio->nome ?? 'Laboratório';
        $obsTexto = linhaObservacaoInscricaoInterlab($laboratorioNome, $valor);

        // Verifica se já existe lançamento vinculado a ESTE inscrito
        $lancamentoIndividual = $inscrito->lancamentoFinanceiro;

        if ($lancamentoIndividual) {
            // Atualiza lançamento existente
            $lancamentoIndividual->update([
                'valor' => formataMoeda($valor),
                'observacoes' => $obsTexto,
                'data_emissao' => now(),
            ]);

            return $lancamentoIndividual;
        }

        // Cria o NOVO lançamento individual
        $novoLancamento = LancamentoFinanceiro::create([
            'pessoa_id' => $empresa->id,
            'agenda_interlab_id' => $agenda_interlab->id,
            'historico' => $historico,
            'valor' => formataMoeda($valor),
            'centro_custo_id' => CentroCusto::ID_INTERLABORATORIAL,
            'plano_conta_id' => PlanoConta::ID_RECEITA_PRESTACAO_SERVICOS,
            'tipo_lancamento' => 'CREDITO',
            'data_emissao' => now(),
            'status' => 'PROVISIONADO',
            'observacoes' => $obsTexto,
        ]);

        // VINCULA ao inscrito
        $inscrito->update(['lancamento_financeiro_id' => $novoLancamento->id]);

        return $novoLancamento;
    }

    /**
     * Cancela o lançamento financeiro de uma inscrição
     */
    public function cancelarLancamento(InterlabInscrito $inscrito): void
    {
        $lancamento = $inscrito->lancamentoFinanceiro;

        if ($lancamento) {
            $lancamento->delete();
        }
    }
}

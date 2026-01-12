<?php

namespace App\Actions\Financeiro;

use App\Models\InterlabInscrito;
use App\Models\LancamentoFinanceiro;

class GerarLancamentoInterlabAction
{
    /**
     * Gera ou atualiza um lançamento financeiro para uma inscrição do Interlab
     *
     * @param InterlabInscrito $inscrito
     * @param float|null $valor
     * @return LancamentoFinanceiro
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

        $historico = 'Inscrição no interlab - ' . ($agenda_interlab->interlab->nome ?? 'N/A');
        $laboratorioNome = $laboratorio->nome ?? 'Laboratório';
        $obsTexto = "Inscrição de {$laboratorioNome}, com valor de R$ {$valor} \n";

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
            'centro_custo_id' => '4', // INTERLABORATORIAL
            'plano_conta_id' => '3', // RECEITA PRESTAÇÃO DE SERVIÇOS
            'data_emissao' => now(),
            'status' => 'PROVISIONADO',
            'observacoes' => $obsTexto
        ]);

        // VINCULA ao inscrito
        $inscrito->update(['lancamento_financeiro_id' => $novoLancamento->id]);
        
        return $novoLancamento;
    }

    /**
     * Cancela o lançamento financeiro de uma inscrição
     * 
     * @param InterlabInscrito $inscrito
     * @return void
     */
    public function cancelarLancamento(InterlabInscrito $inscrito): void
    {
        $lancamento = $inscrito->lancamentoFinanceiro;
        
        if ($lancamento) {
            $lancamento->delete();
        }
    }
}

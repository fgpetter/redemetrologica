<?php

namespace App\Actions\Financeiro;

use App\Actions\Avaliacoes\CalcularOrcamentoAvaliacaoAction;
use App\Mail\LancamentoAvaliacaoNotification;
use App\Models\AgendaAvaliacao;
use App\Models\CentroCusto;
use App\Models\LancamentoFinanceiro;
use App\Models\PlanoConta;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class GerarLancamentoAvaliacaoAction
{
    public function __construct(
        private CalcularOrcamentoAvaliacaoAction $calcularOrcamentoAvaliacaoAction,
    ) {}

    /**
     * Gera o lançamento financeiro de uma avaliação (imutável: se já existir, apenas o retorna).
     */
    public function execute(AgendaAvaliacao $avaliacao): LancamentoFinanceiro
    {
        $lancamento = LancamentoFinanceiro::where('agenda_avaliacao_id', $avaliacao->id)->first();

        if ($lancamento) {
            return $lancamento;
        }

        $avaliacao->loadMissing('laboratorio.pessoa');
        $orcamento = $this->calcularOrcamentoAvaliacaoAction->execute($avaliacao);

        $lancamento = LancamentoFinanceiro::create([
            'pessoa_id' => $avaliacao->laboratorio->pessoa->id,
            'agenda_avaliacao_id' => $avaliacao->id,
            'historico' => 'Avaliação - '.$avaliacao->laboratorio->nome_laboratorio.' - '.Carbon::parse($avaliacao->data_inicio)->format('d/m/Y'),
            'valor' => $orcamento['valor_proposta'],
            'centro_custo_id' => CentroCusto::ID_AVALIACAO,
            'plano_conta_id' => PlanoConta::ID_RECEITA_PRESTACAO_SERVICOS,
            'tipo_lancamento' => 'CREDITO',
            'data_emissao' => now(),
            'status' => 'PROVISIONADO',
        ]);

        Mail::to('financeiro@redemetrologica.com.br')->send(new LancamentoAvaliacaoNotification($avaliacao, $lancamento));

        return $lancamento;
    }
}

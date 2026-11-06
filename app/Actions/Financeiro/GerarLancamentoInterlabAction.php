<?php

namespace App\Actions\Financeiro;

use App\Models\CentroCusto;
use App\Models\InterlabInscrito;
use App\Models\LancamentoFinanceiro;
use App\Models\PlanoConta;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class GerarLancamentoInterlabAction
{
    /**
     * Gera ou atualiza um lançamento financeiro para uma inscrição do Interlab
     *
     * @param  InterlabInscrito  $inscrito  Inscrição que recebe o lançamento
     * @param  array<string, mixed>|null  $dadosCobranca  Respostas de cobrança da empresa (colunas do lançamento)
     * @return LancamentoFinanceiro Lançamento vinculado à inscrição
     */
    public function execute(InterlabInscrito $inscrito, float|int|string|null $valor = null, ?array $dadosCobranca = null): LancamentoFinanceiro
    {
        return DB::transaction(function () use ($inscrito, $valor, $dadosCobranca): LancamentoFinanceiro {
            $inscricaoPersistida = InterlabInscrito::query()->lockForUpdate()->findOrFail($inscrito->getKey());

            $lancamento = $this->salvarLancamento(
                $inscricaoPersistida,
                $valor ?? $inscrito->valor,
                Arr::only($dadosCobranca ?? [], LancamentoFinanceiro::CAMPOS_COBRANCA),
            );

            $inscrito->lancamento_financeiro_id = $lancamento->id;
            $inscrito->syncOriginalAttribute('lancamento_financeiro_id');
            $inscrito->setRelation('lancamentoFinanceiro', $lancamento);

            return $lancamento;
        });
    }

    /**
     * @param  array<string, mixed>  $dadosCobranca
     */
    private function salvarLancamento(InterlabInscrito $inscrito, float|int|string|null $valor, array $dadosCobranca): LancamentoFinanceiro
    {
        // Garante que os relacionamentos necessários estejam carregados
        $inscrito->loadMissing(['agendaInterlab.interlab', 'empresa', 'laboratorio', 'lancamentoFinanceiro']);

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
                ...$dadosCobranca,
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
            ...$dadosCobranca,
        ]);

        // VINCULA ao inscrito
        $inscrito->update(['lancamento_financeiro_id' => $novoLancamento->id]);

        return $novoLancamento;
    }

    /**
     * Atualiza somente as respostas de cobrança do lançamento de uma inscrição do Interlab
     *
     * @param  array<string, mixed>  $dadosCobranca
     */
    public function atualizarDadosCobranca(InterlabInscrito $inscrito, array $dadosCobranca): void
    {
        $lancamento = $inscrito->lancamentoFinanceiro;

        if ($lancamento) {
            $lancamento->update(Arr::only($dadosCobranca, LancamentoFinanceiro::CAMPOS_COBRANCA));
        }
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

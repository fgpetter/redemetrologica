<?php

namespace App\Actions\Financeiro;

use App\Models\AgendaCursos;
use App\Models\CentroCusto;
use App\Models\CursoInscrito;
use App\Models\LancamentoFinanceiro;
use App\Models\Pessoa;
use App\Models\PlanoConta;
use Illuminate\Support\Carbon;

class GerarLancamentoCursoAction
{
    /**
     * Gera ou atualiza lançamento financeiro para inscrição em curso.
     *
     * @param  Pessoa  $inscrito  Pessoa vinculada à inscrição
     * @param  Pessoa|null  $empresa  Empresa contratante (se CNPJ)
     * @param  bool  $associado  Se a empresa/pessoa é associada
     * @param  string|null  $valor  Valor manual (opcional)
     * @param  string|null  $nomeParticipante  Nome do participante para observação
     */
    public function execute(
        AgendaCursos $agendacurso,
        Pessoa $inscrito,
        ?Pessoa $empresa = null,
        bool $associado = false,
        ?string $valor = null,
        ?string $nomeParticipante = null
    ): LancamentoFinanceiro {
        if (! $valor) {
            $valor = ($associado) ? $agendacurso->investimento_associado : $agendacurso->investimento;
        }

        // se a inscrição está associada a uma empresa
        if ($empresa) {

            $lancamento = LancamentoFinanceiro::where('pessoa_id', $empresa->id)
                ->where('agenda_curso_id', $agendacurso->id)
                ->first();

            // se a empresa não possui inscritos nesse curso, cria um novo lançamento
            if (! $lancamento) {
                $lancamento = LancamentoFinanceiro::create([
                    'pessoa_id' => $empresa->id,
                    'agenda_curso_id' => $agendacurso->id,
                    'historico' => 'Inscrição no curso - '.$agendacurso->curso->descricao,
                    'valor' => formataMoeda($valor),
                    'centro_custo_id' => CentroCusto::ID_TREINAMENTO,
                    'plano_conta_id' => PlanoConta::ID_RECEITA_PRESTACAO_SERVICOS,
                    'tipo_lancamento' => 'CREDITO',
                    'data_emissao' => now(),
                    'status' => 'PROVISIONADO',
                ]);
            } else { // se a empresa já possui inscritos nesse curso, atualiza o valor

                $dados_empresa = CursoInscrito::where('empresa_id', $empresa->id)
                    ->where('agenda_curso_id', $agendacurso->id)
                    ->with('pessoa')
                    ->get();

                $observacoes = '';
                foreach ($dados_empresa as $dado) {
                    $data = Carbon::parse($dado->data_inscricao)->format('d/m/Y H:i');
                    $observacoes .= linhaObservacaoInscricao($dado->nome, $dado->valor, $data);
                }

                $lancamento->update([
                    'valor' => $dados_empresa->sum('valor'),
                    'observacoes' => $observacoes,
                ]);
            }

        } else { // se a inscrição é de pessoa física
            $nomeObs = $nomeParticipante ?? $inscrito->nome_razao;

            $lancamento = LancamentoFinanceiro::updateOrCreate(
                [
                    'pessoa_id' => $inscrito->id,
                    'agenda_curso_id' => $agendacurso->id,
                ],
                [
                    'historico' => 'Inscrição no curso - '.$agendacurso->curso->descricao,
                    'valor' => formataMoeda($valor),
                    'centro_custo_id' => CentroCusto::ID_TREINAMENTO,
                    'plano_conta_id' => PlanoConta::ID_RECEITA_PRESTACAO_SERVICOS,
                    'tipo_lancamento' => 'CREDITO',
                    'data_emissao' => now(),
                    'status' => 'PROVISIONADO',
                    'observacoes' => linhaObservacaoInscricao($nomeObs, $valor),
                ]
            );

        }

        return $lancamento;
    }
}

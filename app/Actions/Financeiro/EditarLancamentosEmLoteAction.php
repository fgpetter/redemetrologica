<?php

namespace App\Actions\Financeiro;

use App\Exceptions\OperacaoImpedidaException;
use App\Models\LancamentoFinanceiro;
use App\Support\Observabilidade\ResumoExecucao;
use Illuminate\Support\Facades\DB;

class EditarLancamentosEmLoteAction
{
    /**
     * Atualiza em lote campos opcionais de lançamentos financeiros (todos do mesmo tipo: crédito ou débito).
     *
     * @param  array<int, string>  $uids
     * @param  array{consiliacao?: string|null, nota_fiscal?: string|null, data_pagamento?: string|null, data_vencimento?: string|null}  $input
     * @return int Número de lançamentos atualizados (0 se não houver campos para persistir)
     */
    public function execute(array $uids, array $input, ?ResumoExecucao $resumo = null): int
    {
        $uniqueUids = array_values(array_unique(array_filter($uids, fn ($uid) => is_string($uid) && $uid !== '')));

        if ($uniqueUids === []) {
            return 0;
        }

        $campos = $this->filtrarCamposParaPersistir($input);

        if ($campos === []) {
            return 0;
        }

        if (array_key_exists('data_pagamento', $campos)) {
            $campos['status'] = $campos['data_pagamento'] ? 'EFETIVADO' : 'PROVISIONADO';
        }

        $resumo ??= new ResumoExecucao('financeiro.editar_lote');

        return $resumo->etapa('transacao_lote', fn () => DB::transaction(function () use ($uniqueUids, $campos, $resumo): int {
            $lancamentos = $resumo->etapa('selecionar_lancamentos', fn () => LancamentoFinanceiro::query()
                ->whereIn('uid', $uniqueUids)
                ->lockForUpdate()
                ->get());

            $resumo->etapa('validar_lote', function () use ($lancamentos, $uniqueUids): void {
                if ($lancamentos->count() !== count($uniqueUids)) {
                    throw new OperacaoImpedidaException('referencias_ausentes', 'Um ou mais lançamentos não foram encontrados.');
                }

                if ($lancamentos->pluck('tipo_lancamento')->unique()->count() > 1) {
                    throw new OperacaoImpedidaException('tipos_distintos', 'Não é permitido atualizar receitas e despesas no mesmo lote.');
                }
            });

            return $resumo->etapa('atualizar_lancamentos', function () use ($lancamentos, $campos): int {
                foreach ($lancamentos as $lancamento) {
                    $lancamento->update($campos);
                }

                return $lancamentos->count();
            });
        }));
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    private function filtrarCamposParaPersistir(array $input): array
    {
        $campos = [];

        foreach (['consiliacao', 'nota_fiscal', 'data_pagamento', 'data_vencimento'] as $campo) {
            if (! array_key_exists($campo, $input)) {
                continue;
            }

            $valor = $input[$campo];

            if ($valor === null) {
                continue;
            }

            if (is_string($valor)) {
                $valor = trim($valor);
                if ($valor === '') {
                    continue;
                }
            }

            $campos[$campo] = $valor;
        }

        return $campos;
    }
}

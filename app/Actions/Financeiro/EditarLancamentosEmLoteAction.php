<?php

namespace App\Actions\Financeiro;

use App\Models\LancamentoFinanceiro;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class EditarLancamentosEmLoteAction
{
    /**
     * Atualiza em lote campos opcionais de lançamentos financeiros (todos do mesmo tipo: crédito ou débito).
     *
     * @param  array<int, string>  $uids
     * @param  array{consiliacao?: string|null, nota_fiscal?: string|null, data_pagamento?: string|null, data_vencimento?: string|null}  $input
     * @return int Número de lançamentos atualizados (0 se não houver campos para persistir)
     */
    public function execute(array $uids, array $input): int
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

        return (int) DB::transaction(function () use ($uniqueUids, $campos): int {
            $lancamentos = LancamentoFinanceiro::query()
                ->whereIn('uid', $uniqueUids)
                ->lockForUpdate()
                ->get();

            if ($lancamentos->count() !== count($uniqueUids)) {
                throw new RuntimeException('Um ou mais lançamentos não foram encontrados.');
            }

            $tiposDistintos = $lancamentos->pluck('tipo_lancamento')->unique()->values();

            if ($tiposDistintos->count() > 1) {
                throw new RuntimeException('Não é permitido atualizar receitas e despesas no mesmo lote.');
            }

            foreach ($lancamentos as $lancamento) {
                $lancamento->update($campos);
            }

            return $lancamentos->count();
        });
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

<?php

namespace App\Actions\Avaliacoes;

class CalcularValoresAreaAvaliadaAction
{
    /**
     * Normaliza valores monetários e calcula os campos derivados da área avaliada.
     *
     * @param  array<string, mixed>  $dados
     * @return array{
     *     valor_dia: float|null,
     *     valor_lider: float|null,
     *     valor_estim_desloc: float|null,
     *     valor_estim_alim: float|null,
     *     valor_estim_hosped: float|null,
     *     valor_estim_extras: float|null,
     *     valor_real_desloc: float|null,
     *     valor_real_alim: float|null,
     *     valor_real_hosped: float|null,
     *     valor_real_extras: float|null,
     *     valor_avaliador: float,
     *     total_gastos_estim: float,
     *     total_gastos_reais: float
     * }
     */
    public function execute(array $dados): array
    {
        $valorDia = $this->toFloat(formataMoeda($dados['valor_dia'] ?? null));
        $valorLider = $this->toFloat(formataMoeda($dados['valor_lider'] ?? null));
        $valorEstimDesloc = $this->toFloat(formataMoeda($dados['valor_estim_desloc'] ?? null));
        $valorEstimAlim = $this->toFloat(formataMoeda($dados['valor_estim_alim'] ?? null));
        $valorEstimHosped = $this->toFloat(formataMoeda($dados['valor_estim_hosped'] ?? null));
        $valorEstimExtras = $this->toFloat(formataMoeda($dados['valor_estim_extras'] ?? null));
        $valorRealDesloc = $this->toFloat(formataMoeda($dados['valor_real_desloc'] ?? null));
        $valorRealAlim = $this->toFloat(formataMoeda($dados['valor_real_alim'] ?? null));
        $valorRealHosped = $this->toFloat(formataMoeda($dados['valor_real_hosped'] ?? null));
        $valorRealExtras = $this->toFloat(formataMoeda($dados['valor_real_extras'] ?? null));
        $dias = $this->toFloat($dados['dias'] ?? null);

        return [
            'valor_dia' => $valorDia,
            'valor_lider' => $valorLider,
            'valor_estim_desloc' => $valorEstimDesloc,
            'valor_estim_alim' => $valorEstimAlim,
            'valor_estim_hosped' => $valorEstimHosped,
            'valor_estim_extras' => $valorEstimExtras,
            'valor_real_desloc' => $valorRealDesloc,
            'valor_real_alim' => $valorRealAlim,
            'valor_real_hosped' => $valorRealHosped,
            'valor_real_extras' => $valorRealExtras,
            'valor_avaliador' => ($dias * $valorDia) + $valorLider,
            'total_gastos_estim' => $valorEstimDesloc + $valorEstimAlim + $valorEstimHosped + $valorEstimExtras,
            'total_gastos_reais' => $valorRealDesloc + $valorRealAlim + $valorRealHosped + $valorRealExtras,
        ];
    }

    private function toFloat(mixed $valor): float
    {
        return (float) ($valor ?? 0);
    }
}

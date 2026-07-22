<?php

namespace App\Actions\Interlab;

use App\Models\FornecedorAvaliacao;
use App\Models\InterlabDespesaLancamento;
use Illuminate\Validation\ValidationException;

class SyncFornecedorAvaliacaoAction
{
    public function sync(InterlabDespesaLancamento $lancamento, ?int $custo, ?int $tempo, ?int $qualidade): void
    {
        $preenchidos = array_filter([$custo, $tempo, $qualidade], fn ($v) => $v !== null);
        $totalPreenchidos = count($preenchidos);

        if ($totalPreenchidos === 0) {
            FornecedorAvaliacao::query()
                ->where('interlab_despesa_lancamento_id', $lancamento->id)
                ->delete();

            return;
        }

        if ($totalPreenchidos < 3) {
            throw ValidationException::withMessages([
                'avaliacao' => 'Preencha Custo, Tempo e Qualidade ou deixe todos em branco.',
            ]);
        }

        FornecedorAvaliacao::updateOrCreate(
            [
                'interlab_despesa_lancamento_id' => $lancamento->id,
            ],
            [
                'fornecedor_id' => $lancamento->fornecedor_id,
                'custo' => $custo,
                'tempo' => $tempo,
                'qualidade' => $qualidade,
                'media' => FornecedorAvaliacao::calcularMedia($custo, $tempo, $qualidade),
            ]
        );
    }
}

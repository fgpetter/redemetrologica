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

        $avaliacao = FornecedorAvaliacao::firstOrNew([
            'interlab_despesa_lancamento_id' => $lancamento->id,
        ]);

        if ($avaliacao->exists && (int) $avaliacao->fornecedor_id !== (int) $lancamento->fornecedor_id) {
            throw ValidationException::withMessages([
                'avaliacao' => 'Para trocar o fornecedor, limpe as notas atuais. Depois de salvar, avalie o novo fornecedor.',
            ]);
        }

        $avaliacao->fill([
            'fornecedor_id' => $lancamento->fornecedor_id,
            'custo' => $custo,
            'tempo' => $tempo,
            'qualidade' => $qualidade,
        ]);
        $notasAlteradas = $avaliacao->isDirty(['custo', 'tempo', 'qualidade']);

        if ($totalPreenchidos < 3) {
            if ($avaliacao->exists && ! $notasAlteradas) {
                return;
            }

            throw ValidationException::withMessages([
                'avaliacao' => 'Preencha Custo, Tempo e Qualidade ou deixe todos em branco.',
            ]);
        }

        $avaliacao->media = FornecedorAvaliacao::calcularMedia($custo, $tempo, $qualidade);
        if (! $avaliacao->exists || $notasAlteradas) {
            $avaliacao->avaliador_user_id = auth()->id();
        }
        $avaliacao->save();
    }
}

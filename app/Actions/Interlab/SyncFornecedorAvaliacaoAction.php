<?php

namespace App\Actions\Interlab;

use App\Models\FornecedorAvaliacao;
use App\Models\InterlabDespesa;
use Illuminate\Validation\ValidationException;

class SyncFornecedorAvaliacaoAction
{
    public function sync(int $agendaInterlabId, int $fornecedorId, ?int $custo, ?int $tempo, ?int $qualidade): void
    {
        $preenchidos = array_filter([$custo, $tempo, $qualidade], fn ($v) => $v !== null);
        $totalPreenchidos = count($preenchidos);

        if ($totalPreenchidos === 0) {
            FornecedorAvaliacao::query()
                ->where('agenda_interlab_id', $agendaInterlabId)
                ->where('fornecedor_id', $fornecedorId)
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
                'agenda_interlab_id' => $agendaInterlabId,
                'fornecedor_id' => $fornecedorId,
            ],
            [
                'custo' => $custo,
                'tempo' => $tempo,
                'qualidade' => $qualidade,
                'media' => FornecedorAvaliacao::calcularMedia($custo, $tempo, $qualidade),
            ]
        );
    }

    public function deleteIfSemDespesas(int $agendaInterlabId, int $fornecedorId): void
    {
        $temDespesas = InterlabDespesa::query()
            ->where('agenda_interlab_id', $agendaInterlabId)
            ->where('fornecedor_id', $fornecedorId)
            ->exists();

        if (! $temDespesas) {
            FornecedorAvaliacao::query()
                ->where('agenda_interlab_id', $agendaInterlabId)
                ->where('fornecedor_id', $fornecedorId)
                ->delete();
        }
    }
}

<?php

namespace App\Actions\Interlab;

use App\Models\InterlabDespesaLancamento;
use Illuminate\Support\Facades\DB;

class ExcluirDespesaInterlabAction
{
    /**
     * @param  int  $agendaId  Agenda que deve conter o lançamento
     * @param  int  $lancamentoId  Lançamento a remover
     * @return bool Indica se o lançamento foi encontrado e removido
     */
    public function execute(int $agendaId, int $lancamentoId): bool
    {
        return DB::transaction(function () use ($agendaId, $lancamentoId): bool {
            $lancamento = InterlabDespesaLancamento::query()
                ->where('agenda_interlab_id', $agendaId)
                ->lockForUpdate()
                ->find($lancamentoId);

            if ($lancamento === null) {
                return false;
            }

            $lancamento->itens()->get()->each->delete();
            $lancamento->avaliacao()->delete();
            $lancamento->delete();

            return true;
        });
    }
}

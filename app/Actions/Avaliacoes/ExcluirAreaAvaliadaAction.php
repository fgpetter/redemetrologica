<?php

namespace App\Actions\Avaliacoes;

use App\Models\AreaAvaliada;
use Illuminate\Support\Facades\DB;

class ExcluirAreaAvaliadaAction
{
    /**
     * Remove uma área avaliada.
     */
    public function execute(AreaAvaliada $area): void
    {
        DB::transaction(function () use ($area) {
            $area->delete();
        });
    }
}

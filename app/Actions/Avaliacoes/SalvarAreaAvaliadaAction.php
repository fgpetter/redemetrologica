<?php

namespace App\Actions\Avaliacoes;

use App\Models\AreaAvaliada;
use Illuminate\Support\Facades\DB;

class SalvarAreaAvaliadaAction
{
    public function __construct(
        private CalcularValoresAreaAvaliadaAction $calcularValoresAreaAvaliadaAction,
    ) {}

    /**
     * Cria ou atualiza uma área avaliada com os valores derivados calculados.
     *
     * @param  array<string, mixed>  $dados
     */
    public function execute(?AreaAvaliada $area, array $dados): AreaAvaliada
    {
        return DB::transaction(function () use ($area, $dados) {
            $valores = $this->calcularValoresAreaAvaliadaAction->execute($dados);
            $payload = array_merge($dados, $valores);

            if ($area?->exists) {
                $area->update($payload);

                return $area->fresh();
            }

            return AreaAvaliada::query()->create($payload);
        });
    }
}

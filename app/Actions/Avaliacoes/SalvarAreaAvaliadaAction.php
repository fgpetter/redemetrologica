<?php

namespace App\Actions\Avaliacoes;

use App\Models\AreaAvaliada;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class SalvarAreaAvaliadaAction
{
    public function __construct(
        private CalcularValoresAreaAvaliadaAction $calcularValoresAreaAvaliadaAction,
    ) {}

    /**
     * Cria ou atualiza uma área avaliada com os valores derivados calculados.
     *
     * @param  AreaAvaliada|null  $area  Área existente na edição
     * @param  array<string, mixed>  $dados  Campos validados e valores monetários a calcular
     * @return AreaAvaliada Área persistida com os valores derivados
     */
    public function execute(?AreaAvaliada $area, array $dados): AreaAvaliada
    {
        return DB::transaction(function () use ($area, $dados) {
            $valores = $this->calcularValoresAreaAvaliadaAction->execute($dados);
            $payload = array_merge(Arr::only($dados, [
                'avaliacao_id',
                'area_atuacao_id',
                'situacao',
                'data_inicial',
                'data_final',
                'avaliador_id',
                'num_ensaios',
                'dias',
            ]), $valores);

            if ($area?->exists) {
                $area->update($payload);

                return $area->fresh();
            }

            return AreaAvaliada::query()->create($payload);
        });
    }
}

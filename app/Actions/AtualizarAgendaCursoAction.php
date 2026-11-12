<?php

namespace App\Actions;

use App\Models\AgendaCursos;
use App\Models\CursoInscrito;
use Illuminate\Support\Facades\DB;

class AtualizarAgendaCursoAction
{
    /**
     * Atualiza a agenda e invalida apenas as reservas de envio pendentes quando a data muda.
     *
     * @param  array<string, mixed>  $dados
     */
    public function execute(AgendaCursos $agenda, array $dados): AgendaCursos
    {
        return DB::transaction(function () use ($agenda, $dados): AgendaCursos {
            $registro = AgendaCursos::query()->lockForUpdate()->findOrFail($agenda->getKey());
            $dataAnterior = $registro->data_inicio?->toDateString();

            $registro->update($dados);

            if ($dataAnterior !== $registro->data_inicio?->toDateString()) {
                $this->invalidarReservasPendentes($registro);
            }

            return $registro;
        });
    }

    private function invalidarReservasPendentes(AgendaCursos $agenda): void
    {
        $reservas = [];

        if ($agenda->conferencia_enviada_em === null && $agenda->conferencia_envio_id !== null) {
            $reservas['conferencia_envio_id'] = null;
        }

        if ($agenda->pendencias_enviadas_em === null && $agenda->pendencias_envio_id !== null) {
            $reservas['pendencias_envio_id'] = null;
        }

        if ($reservas !== []) {
            $agenda->update($reservas);
        }

        $agenda->inscritos()
            ->whereNull('informacoes_enviadas_em')
            ->whereNotNull('informacoes_envio_id')
            ->lockForUpdate()
            ->get()
            ->each(fn (CursoInscrito $inscrito) => $inscrito->update(['informacoes_envio_id' => null]));
    }
}

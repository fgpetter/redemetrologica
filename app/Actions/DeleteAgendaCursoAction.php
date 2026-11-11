<?php

namespace App\Actions;

use App\Models\AgendaCursos;

class DeleteAgendaCursoAction
{
    public function execute(AgendaCursos $agendaCurso): void
    {
        foreach ($agendaCurso->materiaisTreinamento as $material) {
            app(DeleteMaterialTreinamentoAction::class)->execute($material);
        }

        $agendaCurso->delete();
    }
}

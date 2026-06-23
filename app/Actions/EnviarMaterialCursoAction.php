<?php

namespace App\Actions;

use App\Models\DadosGeraDoc;
use App\Models\CursoInscrito;
use App\Jobs\EnviarLinkMaterialCursoJob;

class EnviarMaterialCursoAction
{
    /**
     * Cria registro de log e dispara e-mail com materiais
     *
     * @param CursoInscrito $inscrito
     * @return DadosGeraDoc
     */
    public function execute(CursoInscrito $inscrito): DadosGeraDoc
    {
        $inscrito->load(['agendaCurso.curso', 'empresa']);

        $dadosDoc = DadosGeraDoc::create([
            'content' => [
                'participante_id' => $inscrito->id,
                'participante_nome' => $inscrito->nome,
                'participante_email' => $inscrito->email,
                'curso_nome' => $inscrito->agendaCurso->curso->descricao,
                'empresa_nome' => $inscrito->empresa->nome_razao ?? null,
            ],
            'tipo' => 'material_curso',
        ]);

        EnviarLinkMaterialCursoJob::dispatch($dadosDoc->id);

        return $dadosDoc;
    }
}

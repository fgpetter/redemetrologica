<?php

namespace App\Actions;

use App\Models\DadosGeraDoc;
use App\Models\CursoInscrito;
use App\Jobs\EnviarLinkCertificadoJob;

class EnviarCertificadoAction
{
    /**
     * Cria registro de log e dispara e-mail com link do certificado
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
                'curso_data' => ($inscrito->agendaCurso->data_inicio instanceof \Carbon\Carbon ? $inscrito->agendaCurso->data_inicio->format('d/m/Y') : $inscrito->agendaCurso->data_inicio) . 
                                ($inscrito->agendaCurso->data_fim instanceof \Carbon\Carbon ? ' a ' . $inscrito->agendaCurso->data_fim->format('d/m/Y') : ''),
                'empresa_nome' => $inscrito->empresa->nome_razao ?? null,
            ],
            'tipo' => 'certificado',
        ]);

        EnviarLinkCertificadoJob::dispatch($dadosDoc->id);

        return $dadosDoc;
    }
}

<?php

namespace App\Actions;

use App\Models\DadosGeraDoc;
use App\Models\InterlabInscrito;
use App\Jobs\EnviarLinkCertificadoInterlabJob;

class EnviarCertificadoInterlabAction
{
    /**
     * Cria registro de log e dispara e-mail com link do certificado
     *
     * @param InterlabInscrito $inscrito
     * @return DadosGeraDoc
     */
    public function execute(InterlabInscrito $inscrito, int $delay = 0): DadosGeraDoc
    {
        $inscrito->load(['laboratorio', 'agendaInterlab.interlab']);

        $dadosDoc = DadosGeraDoc::create([
            'content' => [
                'participante_id' => $inscrito->id,
                'laboratorio_nome' => $inscrito->laboratorio->nome ?? 'Laboratório',
                'laboratorio_email' => $inscrito->laboratorio->email ?? throw new \Exception('E-mail do laboratório não encontrado'),
                'interlab_nome' => $inscrito->agendaInterlab->interlab->nome,
                'interlab_data' => $inscrito->agendaInterlab->data_inicio?->format('d/m/Y'),
            ],
            'tipo' => 'certificado_interlab',
        ]);

        $inscrito->update([
            'certificado_emitido' => now(),
            'certificado_path' => $dadosDoc->suggested_storage_path,
        ]);

      
        EnviarLinkCertificadoInterlabJob::dispatch($dadosDoc->id)->delay(now()->addSeconds($delay));


        return $dadosDoc;
    }
}

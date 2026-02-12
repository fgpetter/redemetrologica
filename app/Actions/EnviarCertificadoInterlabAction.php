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
     * @param string|null $email
     * @param int $delay
     * @return DadosGeraDoc
     */
    public function execute(InterlabInscrito $inscrito, ?string $email = null, int $delay = 0): DadosGeraDoc
    {
        $inscrito->load(['laboratorio', 'agendaInterlab.interlab']);

        $dadosDoc = DadosGeraDoc::create([
            'content' => [
                'participante_id' => $inscrito->id,
                'laboratorio_nome' => $inscrito->laboratorio->nome ?? 'Laboratório',
                'laboratorio_email' => $email ?? $inscrito->email ?? throw new \Exception('E-mail não informado'),
                'interlab_nome' => $inscrito->agendaInterlab->interlab->nome,
                'interlab_data' => $inscrito->agendaInterlab->data_inicio?->format('d/m/Y'),
            ],
            'tipo' => 'certificado_interlab',
        ]);

        $inscrito->update([
            'certificado_emitido' => now(),
            'certificado_path' => $dadosDoc->storage_path,
        ]);

      
        EnviarLinkCertificadoInterlabJob::dispatch($dadosDoc->id)->delay(now()->addSeconds($delay));


        return $dadosDoc;
    }
}

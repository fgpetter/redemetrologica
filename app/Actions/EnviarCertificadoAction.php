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
    public function execute(CursoInscrito $inscrito, int $delay = 0): DadosGeraDoc
    {
        $inscrito->load(['agendaCurso.curso', 'empresa']);

        $dadosDoc = DadosGeraDoc::create([
            'content' => [
                'participante_id' => $inscrito->id,
                'participante_nome' => $inscrito->nome,
                'participante_email' => $inscrito->email,
                'curso_nome' => $inscrito->agendaCurso->curso->descricao,
                'curso_data' => $this->gerarDataFormatada($inscrito->agendaCurso->data_inicio, $inscrito->agendaCurso->data_fim),
                'empresa_nome' => $inscrito->empresa->nome_razao ?? null,
            ],
            'tipo' => 'certificado',
        ]);

        $inscrito->update([
            'certificado_emitido' => now(),
            'certificado_path' => $dadosDoc->suggested_storage_path,
        ]);

      
        EnviarLinkCertificadoJob::dispatch($dadosDoc->id)->delay(now()->addSeconds($delay));


        return $dadosDoc;
    }

    private function gerarDataFormatada(\Carbon\Carbon $dataInicio, \Carbon\Carbon $dataFim): string
    {
        if ($dataInicio->format('d/m/Y') !== $dataFim->format('d/m/Y')) {
            
            if ($dataInicio->diffInDays($dataFim) > 1) {
                // Realizado nos dias: 10/03/2026 a 12/03/2026
                return "Realizado nos dias: " . $dataInicio->format('d/m/Y') . ' a ' . $dataFim->format('d/m/Y');
            }

            // Realizado nos dias: 10 e 11/03/2026
            return "Realizado nos dias: " . $dataInicio->format('d') . ' e ' . $dataFim->format('d/m/Y');
        }

        // Realizado no dia: 10/03/2026
        return "Realizado no dia: " . $dataInicio->format('d/m/Y');
    }
}

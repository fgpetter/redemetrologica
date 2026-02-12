<?php

namespace App\Jobs;

use App\Models\DadosGeraDoc;
use Illuminate\Support\Facades\Mail;
use App\Mail\CertificadoInterlabMail;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class EnviarLinkCertificadoInterlabJob implements ShouldQueue
{
    use Queueable;

    public $dadosDocId;
    public $tries = 3;

    public function __construct($dadosDocId)
    {
        $this->dadosDocId = $dadosDocId;
    }

    public function handle(): void
    {
        try {
            $dadosDoc = DadosGeraDoc::findOrFail($this->dadosDocId);

            Mail::to($dadosDoc->content['laboratorio_email'])
                ->queue(new CertificadoInterlabMail($dadosDoc));

        } catch (\Exception $e) {
            report($e);
        }
    }
}

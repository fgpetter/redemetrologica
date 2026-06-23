<?php

namespace App\Jobs;

use App\Exceptions\InvalidEmailException;
use App\Mail\CertificadoInterlabMail;
use App\Models\DadosGeraDoc;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class GerarCertificadoInterlabJob implements ShouldQueue
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

            if (empty($dadosDoc->content['laboratorio_email'])) {
                $content = [
                    'class' => self::class,
                    'dadosDoc_id' => $dadosDoc->id,
                ];
                new InvalidEmailException($content);
            } else {
                Mail::to($dadosDoc->content['laboratorio_email'])
                    ->sendNow(new CertificadoInterlabMail($dadosDoc));
            }

        } catch (\Exception $e) {
            report($e);
        }
    }
}

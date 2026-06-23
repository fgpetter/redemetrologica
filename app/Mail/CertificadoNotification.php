<?php

namespace App\Mail;

use App\Models\DadosGeraDoc;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CertificadoNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 120;
    public $dadosDoc;

    public function __construct(DadosGeraDoc $dadosDoc)
    {
        $this->dadosDoc = $dadosDoc;
    }

    public function build()
    {
        return $this->subject('Certificado Disponível - ' . $this->dadosDoc->content['curso_nome'])
            ->replyTo('cursos@redemetrologica.com.br')
            ->view('emails.certificado-notification');
    }
}

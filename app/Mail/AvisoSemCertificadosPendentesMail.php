<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class AvisoSemCertificadosPendentesMail extends Mailable
{
    use Queueable;

    public function __construct(private string $assunto, public string $mensagem) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->assunto);
    }

    public function content(): Content
    {
        return new Content(text: 'emails.aviso-sem-certificados-pendentes');
    }
}

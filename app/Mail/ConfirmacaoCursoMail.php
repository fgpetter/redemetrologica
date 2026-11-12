<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConfirmacaoCursoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $curso, public string $dataInicio) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmação de Realização - '.$this->curso,
            replyTo: [new Address('eventos@redemetrologica.com.br')],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.confirmacao-curso');
    }
}

<?php

namespace App\Mail;

use App\Models\DadosGeraDoc;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LinkSenhaAnalistaNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(public DadosGeraDoc $dadosDoc) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address('interlab@redemetrologica.com.br')],
            subject: 'Código de Identificação - '.$this->dadosDoc->content['interlab_nome'],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.link-senha-analista',
        );
    }
}

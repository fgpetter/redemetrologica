<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SuporteDemandaAtualizadaMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(public string $titulo, public string $url) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address('ti@redemetrologica.com.br')],
            subject: 'Demanda de suporte atualizada — '.$this->titulo,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.suporte-demanda-atualizada');
    }

    public function attachments(): array
    {
        return [];
    }
}

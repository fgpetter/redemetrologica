<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class Fr98Mail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<string, mixed>  $dados
     */
    public function __construct(public array $dados) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('eventos@redemetrologica.com.br', (string) config('mail.from.name')),
            cc: [new Address('eventos@redemetrologica.com.br')],
            replyTo: [new Address('eventos@redemetrologica.com.br')],
            subject: 'FR98 — Instruções para o instrutor — '.$this->dados['curso'],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.fr98');
    }

    public function attachments(): array
    {
        return [];
    }
}

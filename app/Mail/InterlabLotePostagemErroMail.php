<?php

namespace App\Mail;

use App\Models\InterlabLotePostagem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InterlabLotePostagemErroMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(public InterlabLotePostagem $lote) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address('interlab@redemetrologica.com.br', 'Interlaboratoriais Rede Metrológica RS')],
            subject: 'Lote de postagem encerrado com erro — '.$this->lote->nome,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.interlab-lote-postagem-erro',
        );
    }

    /**
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InformacoesTreinamentoMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @param  array<string, mixed>  $dados
     */
    public function __construct(public array $dados) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Material e informações de acesso - '.$this->dados['treinamento'], replyTo: [new Address('eventos@redemetrologica.com.br')]);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.informacoes-treinamento');
    }

    public function attachments(): array
    {
        return [];
    }
}

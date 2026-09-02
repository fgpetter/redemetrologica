<?php

namespace App\Mail;

use App\Models\AgendaAvaliacao;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PesquisaSatisfacaoMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(public AgendaAvaliacao $avaliacao)
    {
        $this->avaliacao->loadMissing('laboratorio');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address('avaliacao@redemetrologica.com.br')],
            subject: 'Pesquisa de Satisfação - '.$this->avaliacao->laboratorio->nome_laboratorio,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.pesquisa-satisfacao');
    }
}

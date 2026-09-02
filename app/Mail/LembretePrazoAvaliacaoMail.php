<?php

namespace App\Mail;

use App\Models\AgendaAvaliacao;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LembretePrazoAvaliacaoMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public string $dataPrazoFormatada;

    public function __construct(
        public AgendaAvaliacao $avaliacao,
        public string $rotuloPrazo,
        string $dataPrazo,
    ) {
        $this->avaliacao->loadMissing('laboratorio');
        $this->dataPrazoFormatada = CarbonImmutable::parse($dataPrazo)->format('d/m/Y');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address('avaliacao@redemetrologica.com.br')],
            subject: 'Lembrete de prazo - '.$this->rotuloPrazo.' - '.$this->avaliacao->laboratorio->nome_laboratorio,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.lembrete-prazo-avaliacao');
    }
}

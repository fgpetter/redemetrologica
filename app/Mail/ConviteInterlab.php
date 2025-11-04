<?php

namespace App\Mail;

use App\Models\Convite;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ConviteInterlab extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    
    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public $timeout = 120;

    public array $dados_email = [];

    /**
     * Create a new message instance.
     */
    public function __construct(protected Convite $convite)
    {
        $this->dados_email['pessoa_convidada'] = Str::title($this->convite->nome); // Pessoa Convidada
        $this->dados_email['email_enviado'] = $this->convite->email; // email que foi convidado
        $this->dados_email['pessoa_que_convida'] = Str::title($this->convite->pessoa->nome_razao); // Pessoa que convida
        $this->dados_email['nome_interlab'] = Str::title($this->convite->agendaInterlab->interlab->nome); // Nome do PEP
        $this->dados_email['link_interlab'] = "https://redemetrologica.com.br/interlaboratorial/{$this->convite->agendaInterlab->interlab->uid}"; // Link do PEP
        $this->dados_email['data_inicio'] = ($this->convite->agendaInterlab->data_inicio) ? Carbon::parse($this->convite->agendaInterlab->data_inicio)->format('d/m/Y') : ''; // Data do Curso
        $this->dados_email['link'] = "https://redemetrologica.com.br/interlab/inscricao?referer={$this->convite->empresaUid()}&target={$this->convite->agendaInterlab->uid}"; // Link para inscrição
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        logger('Enviando convite para ' . $this->convite->email);
        return new Envelope(
            replyTo: [
                new Address('interlab@redemetrologica.com.br', 'Interlaboriais Rede Metrológica RS'),
            ],
            subject: 'Inscrição em ' . Str::title($this->convite->agendaInterlab->interlab->nome),
            from: new Address('interlab@redemetrologica.com.br', 'Interlaboriais Rede Metrológica RS'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.convite-interlab',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

<?php

namespace App\Mail;

use App\Models\Convite;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;


class ConviteCurso extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    public $tries = 3;
    public $timeout = 120;

    public $dados_email = [];

    /**
     * Create a new message instance.
     */
    public function __construct(protected Convite $convite)
    {
        $this->dados_email['pessoa_convidada'] = Str::title($this->convite->nome); // Pessoa Convidada
        $this->dados_email['email_enviado'] = $this->convite->email; // email que foi convidado
        $this->dados_email['pessoa_que_convida'] = Str::title($this->convite->pessoa->nome_razao); // Pessoa que convida
        $this->dados_email['nome_curso'] = Str::title($this->convite->agendaCurso->curso->descricao); // Nome do Curso
        $this->dados_email['data_inicio'] = ($this->convite->agendaCurso->data_inicio) ? Carbon::parse($this->convite->agendaCurso->data_inicio)->format('d/m/Y') : ''; // Data do Curso
        $this->dados_email['data_fim'] = ($this->convite->agendaCurso->data_fim) ? Carbon::parse($this->convite->agendaCurso->data_fim)->format('d/m/Y') : ''; // Data do Curso
        $this->dados_email['horario'] = $this->convite->agendaCurso->horario; // Data do Curso
        $this->dados_email['link'] = "https://redemetrologica.com.br/curso/inscricao?referer={$this->convite->pessoa->uid}&target={$this->convite->agendaCurso->uid}"; // Link para inscrição
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            //from: new Address('sistema@redemetrologica.com.br', 'Sistema Rede Metrológica RS'),
            replyTo: [
                new Address('cursos@redemetrologica.com.br', 'Cursos Rede Metrológica RS'),
            ],
            subject: 'Inscrição no curso ' . Str::title($this->convite->agendaCurso->curso->descricao),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.convite-curso',
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

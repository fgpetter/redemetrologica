<?php

namespace App\Mail;

use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class NovoCadastroInterlabNotification extends Mailable implements ShouldQueue
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
    public function __construct( $inscrito, $agenda_interlab )
    {
        $this->dados_email = [
            'interlab_nome' => $agenda_interlab->interlab->nome,
            'nome_da_pessoa' => $inscrito->pessoa->nome_razao,
            'laboratorio_nome' => $inscrito->laboratorio->nome,
            'empresa_nome' => $inscrito->empresa->nome_razao,
            'empresa_cnpj' => $inscrito->empresa->cpf_cnpj,
            'laboratorio_email' => $inscrito->laboratorio->email,
            'laboratorio_telefone' => $inscrito->laboratorio->telefone,
            'laboratorio_endereco' => $inscrito->laboratorio->endereco->endereco.' - '.
                $inscrito->laboratorio->endereco->complemento.' - '.
                $inscrito->laboratorio->endereco->bairro.' - '.
                $inscrito->laboratorio->endereco->cidade.' / '.
                $inscrito->laboratorio->endereco->uf.' - '.
                $inscrito->laboratorio->endereco->cep
        ];
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Novo Inscrito em Interlab' . Str::title($this->dados_email['interlab_nome']),
            replyTo: [
                new Address('interlab@redemetrologica.com.br', 'Interlaboriais Rede Metrológica RS'),
            ],
            from: new Address('interlab@redemetrologica.com.br', 'Interlaboriais Rede Metrológica RS'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.novo-inscrito-interlab',
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

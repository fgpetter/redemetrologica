<?php

namespace App\Mail;

use App\Models\InterlabInscrito;
use App\Models\InterlabRodada;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DocumentoRodadaMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public InterlabInscrito $inscrito;

    public InterlabRodada $rodada;

    public string $tipoDocumento;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public $timeout = 120;

    /**
     * Create a new message instance.
     */
    public function __construct(InterlabInscrito $inscrito, InterlabRodada $rodada, string $tipoDocumento)
    {
        $this->inscrito = $inscrito;
        $this->rodada = $rodada;
        $this->tipoDocumento = $tipoDocumento;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $sufixos = [
            'envio_amostras' => 'Documento para Envio de Amostras',
            'inicio_ensaios' => 'Documento para Início de Ensaios',
            'limite_envio_resultados' => 'Documento para Limite de Envio de Resultados',
            'divulgacao_relatorios' => 'Documento para Divulgação de Relatórios',
        ];

        $subject = 'Rodada '.$this->rodada->descricao.' - '.($sufixos[$this->tipoDocumento] ?? 'Documento de Rodada');

        return new Envelope(
            replyTo: [
                new Address('interlab@redemetrologica.com.br', 'Interlaboriais Rede Metrológica RS'),
            ],
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $views = [
            'envio_amostras' => 'emails.documento-rodada-envio-amostras',
            'inicio_ensaios' => 'emails.documento-rodada-inicio-ensaios',
            'limite_envio_resultados' => 'emails.documento-rodada-limite-envio-resultados',
            'divulgacao_relatorios' => 'emails.documento-rodada-divulgacao-relatorios',
        ];

        return new Content(
            view: $views[$this->tipoDocumento],
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

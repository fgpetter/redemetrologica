<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\InterlabInscrito;

class CertificadoInterlabMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $participante;
    public $pdfPath;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public $timeout = 120;

    /**
     * The delay time in seconds before sending the email.
     */
    public $delay = 5;

    /**
     * Create a new message instance.
     */
    public function __construct(InterlabInscrito $participante, $pdfPath)
    {
        $this->participante = $participante;
        $this->pdfPath = $pdfPath;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    { 
        return new Envelope(
            subject: 'Certificado de Participação - '. $this->participante->agendaInterlab->interlab->nome,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.certificado-interlab',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    { 
        return [
            \Illuminate\Mail\Mailables\Attachment::fromPath($this->pdfPath)
                ->as('certificado.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
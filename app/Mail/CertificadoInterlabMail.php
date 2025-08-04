<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\InterlabInscrito;

class CertificadoInterlabMail extends Mailable
{
    use Queueable, SerializesModels;

    public $participante;
    public $pdfPath;

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
            subject: 'Seu Certificado de Participação - Rede Metrológica',
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
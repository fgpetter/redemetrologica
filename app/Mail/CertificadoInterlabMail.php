<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\DadosGeraDoc;

class CertificadoInterlabMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $dadosDoc;

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
    public function __construct(DadosGeraDoc $dadosDoc)
    {
        $this->dadosDoc = $dadosDoc;
        $this->delay = 5;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    { 
        return new Envelope(
            replyTo: [
                new Address('interlab@redemetrologica.com.br'),
            ],
            subject: 'Certificado de Participação - '. $this->dadosDoc->content['interlab_nome'],
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
}
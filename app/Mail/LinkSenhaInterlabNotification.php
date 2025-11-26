<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LinkSenhaInterlabNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $participante;
    public $linkUuid;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($participante, $linkUuid)
    {
        $this->participante = $participante;
        $this->linkUuid = $linkUuid;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Código de Identificação - ' . $this->participante->agendaInterlab->interlab->nome)
            ->view('emails.link-senha-interlab');
    }
}

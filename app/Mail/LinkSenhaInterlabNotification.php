<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LinkSenhaInterlabNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $dadosDoc;

    public function __construct($dadosDoc)
    {
        $this->dadosDoc = $dadosDoc;
    }

    public function build()
    {
        return $this->subject('Código de Identificação - ' . $this->dadosDoc->content['interlab_nome'])
            ->view('emails.link-senha-interlab');
    }
}

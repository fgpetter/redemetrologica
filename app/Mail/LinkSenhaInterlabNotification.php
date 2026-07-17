<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class LinkSenhaInterlabNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $tries = 3;

    public $timeout = 120;

    public $dadosDoc;

    public function __construct($dadosDoc)
    {
        $this->dadosDoc = $dadosDoc;
    }

    public function build()
    {
        return $this->subject('Código de Identificação - ' . $this->dadosDoc->content['interlab_nome'])
            ->replyTo('interlab@redemetrologica.com.br')
            ->view('emails.link-senha-interlab');
    }
}

<?php

namespace App\Mail;

use App\Models\AgendaInterlab;
use App\Models\InterlabAnalista;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SenhaAnalistaInterlabNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $tries = 3;

    public $timeout = 120;

    public $dados_email;

    public function __construct(InterlabAnalista $analista, AgendaInterlab $agenda_interlab)
    {
        $this->dados_email = [
            'interlab_nome' => $agenda_interlab->interlab->nome,
            'analista_nome' => $analista->nome,
            'laboratorio_nome' => $analista->interlabInscrito->laboratorio->nome,
            'tag_senha' => $analista->tag_senha,
        ];
    }

    public function build()
    {
        return $this->subject('Código de Identificação - ' . $this->dados_email['interlab_nome'])
            ->replyTo('interlab@redemetrologica.com.br')
            ->view('emails.senha-analista-interlab');
    }
}

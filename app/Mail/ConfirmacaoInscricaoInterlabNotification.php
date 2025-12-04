<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\InterlabInscrito;
use App\Models\AgendaInterlab;
use Illuminate\Contracts\Queue\ShouldQueue;

class ConfirmacaoInscricaoInterlabNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $tries = 3;

    public $timeout = 120;

    public $dados_email;

    public function __construct(InterlabInscrito $inscrito, AgendaInterlab $agenda_interlab)
    {
        $this->dados_email = [
            'interlab_nome' => $agenda_interlab->interlab->nome,
            'nome_da_pessoa' => $inscrito->pessoa->nome_razao,
            'laboratorio_nome' => $inscrito->laboratorio->nome,
            'empresa_nome' => $inscrito->empresa->nome_razao,
            'responsavel_tecnico' => $inscrito->laboratorio->responsavel_tecnico,
            'laboratorio_email' => $inscrito->laboratorio->email,
            'laboratorio_telefone' => $inscrito->laboratorio->telefone,
            'laboratorio_endereco' => $inscrito->laboratorio->endereco->endereco.' - '.
              $inscrito->laboratorio->endereco->complemento.' - '.
              $inscrito->laboratorio->endereco->bairro.' - '.
              $inscrito->laboratorio->endereco->cidade.' / '.
              $inscrito->laboratorio->endereco->uf.' - '.
              $inscrito->laboratorio->endereco->cep,
            'link_interlab' => route('site-single-interlaboratorial', $agenda_interlab->uid)
        ];
    }

    public function build()
    {
        return $this->subject('Confirmação de Inscrição - ' . $this->dados_email['interlab_nome'])
                    ->view('emails/confirmacao-inscricao-interlab');
    }
} 
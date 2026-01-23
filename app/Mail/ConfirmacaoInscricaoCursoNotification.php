<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\CursoInscrito;
use App\Models\AgendaCursos;
use Illuminate\Contracts\Queue\ShouldQueue;

class ConfirmacaoInscricaoCursoNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 120;
    public $dados_email;

    public function __construct($inscrito, AgendaCursos $agenda_curso)
    {
        $this->dados_email = [
            'curso_nome' => $agenda_curso->curso->descricao,
            'curso_data' => $agenda_curso->data_inicio->format('d/m/Y') . ($agenda_curso->data_fim ? ' a ' . $agenda_curso->data_fim->format('d/m/Y') : ''),
            'participante_nome' => is_array($inscrito) ? $inscrito['nome'] : $inscrito->nome,
            'participante_email' => is_array($inscrito) ? $inscrito['email'] : $inscrito->email,
            'participante_telefone' => is_array($inscrito) ? $inscrito['telefone'] : $inscrito->telefone,
            'empresa_nome' => is_array($inscrito) ? ($inscrito['empresa_nome'] ?? null) : ($inscrito->empresa->nome_razao ?? null),
            'link_curso' => route('curso-agendado-show', $agenda_curso->uid)
        ];
    }

    public function build()
    {
        return $this->subject('Confirmação de Inscrição em Curso - ' . $this->dados_email['curso_nome'])
                    ->replyTo('cursos@redemetrologica.com.br')
                    ->view('emails.confirmacao-inscricao-curso');
    }
}

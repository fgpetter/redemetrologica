<?php

namespace App\Mail;

use App\Models\DadosGeraDoc;
use App\Models\CursoInscrito;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class MaterialCursoNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 120;
    public $dadosDoc;
    public $inscrito;
    public $materiais;

    public function __construct(DadosGeraDoc $dadosDoc)
    {
        $this->dadosDoc = $dadosDoc;
        $this->inscrito = CursoInscrito::with('agendaCurso.curso.materiais')->find($dadosDoc->content['participante_id']);
        
        $this->materiais = $this->inscrito->agendaCurso->cursoMateriais;
    }

    public function build()
    {
        return $this->subject('Materiais do Curso - ' . $this->inscrito->agendaCurso->curso->descricao)
            ->replyTo('cursos@redemetrologica.com.br')
            ->view('emails.material-curso');
    }
}

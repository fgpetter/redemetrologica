<?php

namespace App\Actions;

use App\Mail\InformacoesTreinamentoMail;
use App\Models\CursoInscrito;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class EnviarMateriaisCursoAction
{
    public function execute(CursoInscrito $inscrito): void
    {
        $email = trim((string) $inscrito->email);
        $validator = Validator::make(['email' => $email], ['email' => ['required', 'email:rfc']]);

        if ($validator->fails()) {
            Log::channel('validation')->warning('E-mail inválido ou ausente para envio manual de materiais de curso.', [
                'agenda_curso_id' => $inscrito->agenda_curso_id,
                'inscrito_id' => $inscrito->getKey(),
            ]);
            $validator->validate();
        }

        Mail::to($email)->queue(
            InformacoesTreinamentoMail::paraInscrito($inscrito->agendaCurso, $inscrito)
                ->onConnection('database'),
        );

        Log::channel('mailsent')->info('E-mail manual de materiais de curso enfileirado.', [
            'agenda_curso_id' => $inscrito->agenda_curso_id,
            'inscrito_id' => $inscrito->getKey(),
        ]);
    }
}

<?php

namespace App\Actions;

use App\Mail\InformacoesTreinamentoMail;
use App\Models\CursoInscrito;
use App\Support\Observabilidade\ResumoExecucao;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class EnviarMateriaisCursoAction
{
    public function execute(CursoInscrito $inscrito, ?ResumoExecucao $resumo = null): void
    {
        $resumo = ResumoExecucao::operacao($resumo, 'EnviarMateriaisCursoAction', [
            'agenda_curso_id' => $inscrito->agenda_curso_id,
            'inscrito_id' => $inscrito->getKey(),
            'inscrito_uid' => $inscrito->uid,
        ], 'mailsent');
        $resumo->resultado('ignorado', 'inelegivel');

        $resumo->executar(fn () => $this->executarObservado($inscrito, $resumo));
    }

    private function executarObservado(CursoInscrito $inscrito, ResumoExecucao $resumo): void
    {
        $email = $resumo->etapa('validar_destinatario', function () use ($inscrito): string {
            $email = trim((string) $inscrito->email);
            $validator = Validator::make(['email' => $email], ['email' => ['required', 'email:rfc']]);

            if ($validator->fails()) {
                Log::channel('validation')->warning('E-mail inválido ou ausente para envio manual de materiais de curso.', [
                    'agenda_curso_id' => $inscrito->agenda_curso_id,
                    'inscrito_id' => $inscrito->getKey(),
                    'inscrito_uid' => $inscrito->uid,
                ]);
                $validator->validate();
            }

            return $email;
        });

        $mensagem = $resumo->etapa('preparar_mensagem', fn () => InformacoesTreinamentoMail::paraInscrito($inscrito->agendaCurso, $inscrito)
            ->onConnection('database'));

        $resumo->etapa('enfileirar', fn () => Mail::to($email)->queue($mensagem));

        $resumo->contexto([
            'agenda_curso_id' => $inscrito->agenda_curso_id,
            'inscrito_id' => $inscrito->getKey(),
            'inscrito_uid' => $inscrito->uid,
        ]);
        $resumo->resultado('enfileirado');
        $resumo->incrementar('enfileirados');
    }
}

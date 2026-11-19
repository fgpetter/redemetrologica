<?php

namespace App\Actions;

use App\Jobs\EnviarInformacoesCursoJob;
use App\Mail\InformacoesTreinamentoMail;
use App\Models\AgendaCursos;
use App\Models\CursoInscrito;
use App\Support\Observabilidade\ResumoExecucao;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EnfileirarEmailInscritoCursoAction
{
    public function execute(int $agendaCursoId, int $inscritoId, string $dataEsperada, ?ResumoExecucao $resumo = null): bool
    {
        $resumo = ResumoExecucao::operacao($resumo, 'EnfileirarEmailInscritoCursoAction', [
            'tipo' => 'participante',
            'agenda_curso_id' => $agendaCursoId,
            'inscrito_id' => $inscritoId,
        ], 'mailsent');
        $resumo->resultado('ignorado', 'inelegivel');

        return $resumo->executar(fn () => $this->executarObservado($agendaCursoId, $inscritoId, $dataEsperada, $resumo));
    }

    private function executarObservado(int $agendaCursoId, int $inscritoId, string $dataEsperada, ResumoExecucao $resumo): bool
    {
        return DB::transaction(function () use ($resumo, $agendaCursoId, $inscritoId, $dataEsperada): bool {
            $agenda = $resumo->etapa('carregar_registros', fn () => AgendaCursos::query()
                ->with(['curso', 'materiaisTreinamento'])
                ->lockForUpdate()
                ->find($agendaCursoId));

            if ($resumo->etapa('validar_elegibilidade', fn () => ! $agenda
                || $agenda->status !== 'CONFIRMADO'
                || ! in_array($agenda->tipo_agendamento, ['ONLINE', 'IN-COMPANY'], true)
                || $agenda->data_inicio?->toDateString() !== $dataEsperada
                || $agenda->informacoes_enviadas_em !== null
                || $agenda->pendencias_enviadas_em !== null
                || blank($agenda->link_aula)
                || $agenda->materiaisTreinamento->isEmpty())) {
                $resumo->resultadoEtapa('validar_elegibilidade', 'ignorado');

                return false;
            }

            $inscrito = $resumo->etapa('carregar_registros', fn () => CursoInscrito::query()
                ->whereKey($inscritoId)
                ->where('agenda_curso_id', $agenda->getKey())
                ->lockForUpdate()
                ->first());

            if ($resumo->etapa('validar_elegibilidade', fn () => ! $inscrito
                || $inscrito->informacoes_enviadas_em !== null
                || $inscrito->informacoes_envio_id !== null)) {
                $resumo->resultadoEtapa('validar_elegibilidade', 'ignorado');

                return false;
            }

            $resumo->contexto([
                'agenda_curso_id' => $agenda->getKey(),
                'agenda_uid' => $agenda->uid,
                'inscrito_id' => $inscrito->getKey(),
                'inscrito_uid' => $inscrito->uid,
            ]);

            $destinatarioValido = $resumo->etapa('validar_destinatario', function () use ($agenda, $inscrito): bool {
                $email = trim((string) $inscrito->email);

                if (Validator::make(['email' => $email], ['email' => ['required', 'email:rfc']])->passes()) {
                    return true;
                }

                Log::channel('validation')->warning('E-mail inválido ou ausente para envio de informações de curso.', [
                    'tipo' => 'participante',
                    'agenda_curso_id' => $agenda->getKey(),
                    'agenda_uid' => $agenda->uid,
                    'inscrito_id' => $inscrito->getKey(),
                    'inscrito_uid' => $inscrito->uid,
                ]);

                return false;
            });

            if (! $destinatarioValido) {
                $resumo->resultadoEtapa('validar_destinatario', 'impedido');

                return false;
            }

            [$tentativa, $snapshot] = $resumo->etapa('preparar_mensagem', function () use ($agenda, $inscrito): array {
                $tentativa = (string) Str::uuid();
                $snapshot = InformacoesTreinamentoMail::paraInscrito($agenda, $inscrito)->dados;

                return [$tentativa, $snapshot];
            });

            $resumo->etapa('confirmar_estado', fn () => $inscrito->update(['informacoes_envio_id' => $tentativa]));

            $resumo->etapa('enfileirar', fn () => Bus::dispatch((new EnviarInformacoesCursoJob(
                $agenda->getKey(),
                $inscrito->getKey(),
                $tentativa,
                $dataEsperada,
                $snapshot,
            ))->onConnection('database')->beforeCommit()));

            $resumo->contexto([
                'tipo' => 'participante',
                'agenda_curso_id' => $agenda->getKey(),
                'agenda_uid' => $agenda->uid,
                'inscrito_id' => $inscrito->getKey(),
                'inscrito_uid' => $inscrito->uid,
                'tentativa_id' => $tentativa,
            ]);
            $resumo->resultado('enfileirado');
            $resumo->incrementar('enfileirados');

            return true;
        });
    }
}

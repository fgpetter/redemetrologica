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
        if ($resumo !== null) {
            return $this->executarObservado($agendaCursoId, $inscritoId, $dataEsperada, $resumo);
        } else {
            $resumo = new ResumoExecucao('EnfileirarEmailInscritoCursoAction', [], 'mailsent');
            $resumo->resultado('ignorado', 'inelegivel');

            return $resumo->executar(fn () => $this->executarObservado($agendaCursoId, $inscritoId, $dataEsperada, $resumo));
        }
    }

    private function executarObservado(int $agendaCursoId, int $inscritoId, string $dataEsperada, ResumoExecucao $resumo): bool
    {
        return $resumo->etapa('validar_gerar_enfileirar', fn () => DB::transaction(function () use ($resumo, $agendaCursoId, $inscritoId, $dataEsperada): bool {
            $agenda = AgendaCursos::query()
                ->with(['curso', 'materiaisTreinamento'])
                ->lockForUpdate()
                ->find($agendaCursoId);

            if (! $agenda
                || $agenda->status !== 'CONFIRMADO'
                || ! in_array($agenda->tipo_agendamento, ['ONLINE', 'IN-COMPANY'], true)
                || $agenda->data_inicio?->toDateString() !== $dataEsperada
                || $agenda->informacoes_enviadas_em !== null
                || $agenda->pendencias_enviadas_em !== null
                || blank($agenda->link_aula)
                || $agenda->materiaisTreinamento->isEmpty()) {
                return false;
            }

            $inscrito = CursoInscrito::query()
                ->whereKey($inscritoId)
                ->where('agenda_curso_id', $agenda->getKey())
                ->lockForUpdate()
                ->first();

            if (! $inscrito
                || $inscrito->informacoes_enviadas_em !== null
                || $inscrito->informacoes_envio_id !== null) {
                return false;
            }

            $email = trim((string) $inscrito->email);

            if (! Validator::make(['email' => $email], ['email' => ['required', 'email:rfc']])->passes()) {
                Log::channel('validation')->warning('E-mail inválido ou ausente para envio de informações de curso.', [
                    'tipo' => 'participante',
                    'agenda_curso_id' => $agenda->getKey(),
                    'agenda_uid' => $agenda->uid,
                    'inscrito_id' => $inscrito->getKey(),
                    'inscrito_uid' => $inscrito->uid,
                ]);

                return false;
            }

            $tentativa = (string) Str::uuid();
            $snapshot = InformacoesTreinamentoMail::paraInscrito($agenda, $inscrito)->dados;

            $inscrito->update(['informacoes_envio_id' => $tentativa]);

            Bus::dispatch((new EnviarInformacoesCursoJob(
                $agenda->getKey(),
                $inscrito->getKey(),
                $tentativa,
                $dataEsperada,
                $snapshot,
            ))->onConnection('database')->beforeCommit());

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
        }));
    }
}

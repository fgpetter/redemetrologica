<?php

namespace App\Actions;

use App\Jobs\EnviarInformacoesCursoJob;
use App\Models\AgendaCursos;
use App\Models\CursoInscrito;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EnfileirarEmailInscritoCursoAction
{
    public function execute(int $agendaCursoId, int $inscritoId, string $dataEsperada): bool
    {
        return DB::transaction(function () use ($agendaCursoId, $inscritoId, $dataEsperada): bool {
            $agenda = AgendaCursos::query()
                ->with(['curso', 'materiaisTreinamento'])
                ->lockForUpdate()
                ->find($agendaCursoId);

            if (! $agenda
                || $agenda->status !== 'CONFIRMADO'
                || $agenda->tipo_agendamento !== 'ONLINE'
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
                    'inscrito_id' => $inscrito->getKey(),
                ]);

                return false;
            }

            $tentativa = (string) Str::uuid();
            $snapshot = [
                'destinatario' => $email,
                'nome' => $inscrito->nome,
                'treinamento' => $agenda->curso->descricao,
                'data_curso' => $dataEsperada,
                'horario' => $agenda->horario,
                'link' => $agenda->link_aula,
                'materiais' => $agenda->materiaisTreinamento->map(fn ($material) => [
                    'descricao' => $material->descricao ?: basename($material->arquivo),
                    'url' => Storage::disk('public')->url($material->arquivo),
                ])->all(),
            ];

            $inscrito->update(['informacoes_envio_id' => $tentativa]);

            Bus::dispatch((new EnviarInformacoesCursoJob(
                $agenda->getKey(),
                $inscrito->getKey(),
                $tentativa,
                $dataEsperada,
                $snapshot,
            ))->onConnection('database')->beforeCommit());

            Log::channel('mailsent')->info('E-mail automático de informações de curso enfileirado.', [
                'tipo' => 'participante',
                'agenda_curso_id' => $agenda->getKey(),
                'inscrito_id' => $inscrito->getKey(),
                'tentativa_id' => $tentativa,
            ]);

            return true;
        });
    }
}

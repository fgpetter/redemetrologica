<?php

namespace App\Actions;

use App\Jobs\EnviarPendenciasCursoJob;
use App\Models\AgendaCursos;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EnfileirarPendenciasCursoAction
{
    public const DESTINATARIO_ADMINISTRATIVO = 'administrativo@redemetrologica.com.br';

    public function execute(int $agendaCursoId, string $dataEsperada): bool
    {
        return DB::transaction(function () use ($agendaCursoId, $dataEsperada): bool {
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
                || $agenda->pendencias_envio_id !== null
                || (filled($agenda->link_aula) && $agenda->materiaisTreinamento->isNotEmpty())) {
                return false;
            }

            if (! Validator::make(
                ['email' => self::DESTINATARIO_ADMINISTRATIVO],
                ['email' => ['required', 'email:rfc']],
            )->passes()) {
                Log::channel('validation')->warning('Destinatário administrativo inválido para aviso de pendências de curso.', [
                    'tipo' => 'pendencias',
                    'agenda_curso_id' => $agenda->getKey(),
                ]);

                return false;
            }

            $tentativa = (string) Str::uuid();
            $snapshot = [
                'destinatario' => self::DESTINATARIO_ADMINISTRATIVO,
                'treinamento' => $agenda->curso->descricao,
                'data_curso' => $dataEsperada,
            ];

            $agenda->update(['pendencias_envio_id' => $tentativa]);

            Bus::dispatch((new EnviarPendenciasCursoJob(
                $agenda->getKey(),
                $tentativa,
                $dataEsperada,
                $snapshot,
            ))->onConnection('database')->beforeCommit());

            Log::channel('mailsent')->info('E-mail automático de pendências de curso enfileirado.', [
                'tipo' => 'pendencias',
                'agenda_curso_id' => $agenda->getKey(),
                'inscrito_id' => null,
                'tentativa_id' => $tentativa,
            ]);

            return true;
        });
    }
}

<?php

namespace App\Actions;

use App\Jobs\EnviarConferenciaCursoJob;
use App\Models\AgendaCursos;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EnfileirarConferenciaCursoAction
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
                || $agenda->conferencia_enviada_em !== null
                || $agenda->conferencia_envio_id !== null) {
                return false;
            }

            if (! Validator::make(
                ['email' => self::DESTINATARIO_ADMINISTRATIVO],
                ['email' => ['required', 'email:rfc']],
            )->passes()) {
                Log::channel('validation')->warning('Destinatário administrativo inválido para aviso de conferência de curso.', [
                    'tipo' => 'conferencia',
                    'agenda_curso_id' => $agenda->getKey(),
                ]);

                return false;
            }

            $tentativa = (string) Str::uuid();
            $snapshot = [
                'destinatario' => self::DESTINATARIO_ADMINISTRATIVO,
                'treinamento' => $agenda->curso->descricao,
                'data_curso' => $dataEsperada,
                'data_anterior' => CarbonImmutable::parse($dataEsperada, 'America/Sao_Paulo')
                    ->subDay()
                    ->format('d/m/Y'),
            ];

            $agenda->update(['conferencia_envio_id' => $tentativa]);

            Bus::dispatch((new EnviarConferenciaCursoJob(
                $agenda->getKey(),
                $tentativa,
                $dataEsperada,
                $snapshot,
            ))->onConnection('database')->beforeCommit());

            Log::channel('mailsent')->info('E-mail automático de conferência de curso enfileirado.', [
                'tipo' => 'conferencia',
                'agenda_curso_id' => $agenda->getKey(),
                'inscrito_id' => null,
                'tentativa_id' => $tentativa,
            ]);

            return true;
        });
    }
}

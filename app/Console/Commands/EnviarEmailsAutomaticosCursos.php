<?php

namespace App\Console\Commands;

use App\Actions\EnfileirarConferenciaCursoAction;
use App\Actions\EnfileirarEmailInscritoCursoAction;
use App\Actions\EnfileirarPendenciasCursoAction;
use App\Models\AgendaCursos;
use Illuminate\Console\Command;
use Throwable;

class EnviarEmailsAutomaticosCursos extends Command
{
    protected $signature = 'cursos:emails-automaticos';

    protected $description = 'Agenda os avisos e materiais de cursos online';

    public function handle(EnfileirarConferenciaCursoAction $enfileirarConferencia, EnfileirarPendenciasCursoAction $enfileirarPendencias, EnfileirarEmailInscritoCursoAction $enfileirarInscrito): int
    {
        $hoje = now('America/Sao_Paulo')->startOfDay();
        $falhou = false;
        $tentar = function (AgendaCursos $agenda, string $data, string $tipo, ?int $inscritoId = null) use ($enfileirarConferencia, $enfileirarPendencias, $enfileirarInscrito, &$falhou): void {
            try {
                if ($tipo === 'participante') {
                    $enfileirarInscrito->execute($agenda, $data, $inscritoId);
                } elseif ($tipo === 'conferencia') {
                    $enfileirarConferencia->execute($agenda, $data);
                } else {
                    $enfileirarPendencias->execute($agenda, $data);
                }
            } catch (Throwable $exception) {
                $falhou = true;
                report($exception);
                logger()->error('Falha ao enfileirar e-mail automático de curso.', [
                    'agenda_curso_id' => $agenda->id, 'inscrito_id' => $inscritoId,
                    'tipo' => $tipo, 'data_curso' => $data, 'exception' => $exception,
                ]);
            }
        };

        foreach ([2, 1] as $dias) {
            $data = $hoje->copy()->addDays($dias)->toDateString();
            $agendas = AgendaCursos::query()->with(['curso', 'inscritos', 'materiaisTreinamento'])
                ->where('status', 'CONFIRMADO')->where('tipo_agendamento', 'ONLINE')
                ->whereDate('data_inicio', $data)->get();

            foreach ($agendas as $agenda) {
                if ($dias === 2) {
                    $tentar($agenda, $data, 'conferencia');
                } elseif (blank($agenda->link_aula) || $agenda->materiaisTreinamento->isEmpty()) {
                    $tentar($agenda, $data, 'pendencias');
                } else {
                    foreach ($agenda->inscritos as $inscrito) {
                        $tentar($agenda, $data, 'participante', $inscrito->id);
                    }
                }
            }
        }

        return $falhou ? self::FAILURE : self::SUCCESS;
    }
}

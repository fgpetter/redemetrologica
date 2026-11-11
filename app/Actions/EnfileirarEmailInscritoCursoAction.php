<?php

namespace App\Actions;

use App\Mail\InformacoesTreinamentoMail;
use App\Models\AgendaCursos;
use App\Models\CursoInscrito;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EnfileirarEmailInscritoCursoAction
{
    /**
     * @param  int  $inscritoId  Identificador do inscrito.
     */
    public function execute(AgendaCursos $agenda, string $data, int $inscritoId): void
    {
        DB::transaction(function () use ($agenda, $data, $inscritoId) {
            $agenda = AgendaCursos::query()->with('materiaisTreinamento')->whereKey($agenda->id)->lockForUpdate()->first();
            if (! $agenda
                || $agenda->data_inicio?->toDateString() !== $data
                || $agenda->status !== 'CONFIRMADO'
                || $agenda->tipo_agendamento !== 'ONLINE') {
                return;
            }

            $registro = CursoInscrito::query()->whereKey($inscritoId)
                ->where('agenda_curso_id', $agenda->id)->lockForUpdate()->first();
            if (! $registro || blank($agenda->link_aula) || $agenda->materiaisTreinamento->isEmpty()) {
                if ($registro && (blank($agenda->link_aula) || $agenda->materiaisTreinamento->isEmpty())) {
                    app(EnfileirarPendenciasCursoAction::class)->execute($agenda, $data);
                }

                return;
            }
            $email = trim((string) $registro->email);
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                Log::warning('E-mail inválido ou ausente para envio de material de curso.', [
                    'inscrito_id' => $registro->id,
                    'agenda_curso_id' => $agenda->id,
                ]);

                return;
            }
            if ($registro->informacoes_enviadas_em) {
                return;
            }
            Mail::to($email)->queue(
                (new InformacoesTreinamentoMail($agenda, $registro, $data))
                    ->onConnection('database')->beforeCommit(),
            );
        });
    }
}

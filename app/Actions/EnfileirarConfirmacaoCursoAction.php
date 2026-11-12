<?php

namespace App\Actions;

use App\Jobs\EnviarConfirmacaoCursoJob;
use App\Models\AgendaCursos;
use App\Models\CursoInscrito;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class EnfileirarConfirmacaoCursoAction
{
    public function execute(CursoInscrito $inscrito, int $delay = 0): bool
    {
        return DB::transaction(function () use ($inscrito, $delay): bool {
            $agenda = AgendaCursos::query()->lockForUpdate()->findOrFail($inscrito->agenda_curso_id);
            $registro = CursoInscrito::query()->lockForUpdate()->findOrFail($inscrito->getKey());

            if ($agenda->status !== 'CONFIRMADO' || $registro->confirmacao_agendada_em !== null) {
                return false;
            }

            $email = trim((string) $registro->email);

            if (Validator::make(['email' => $email], ['email' => ['required', 'email:rfc']])->fails()) {
                Log::channel('validation')->warning('E-mail inválido ou ausente para confirmação de treinamento.', [
                    'agenda_curso_id' => $agenda->getKey(),
                    'inscrito_id' => $registro->getKey(),
                ]);

                return false;
            }

            $registro->update(['confirmacao_agendada_em' => now()]);

            // A fila database e a reserva são persistidas na mesma transação.
            Bus::dispatch((new EnviarConfirmacaoCursoJob(
                $registro->getKey(),
                $email,
                $agenda->curso->descricao,
                $agenda->data_inicio->format('d/m/Y'),
            ))->onConnection('database')->beforeCommit()->delay(now()->addSeconds($delay)));

            return true;
        });
    }
}

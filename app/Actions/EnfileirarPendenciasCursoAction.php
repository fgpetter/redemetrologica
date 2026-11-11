<?php

namespace App\Actions;

use App\Mail\AvisoPendenciasTreinamentoMail;
use App\Models\AgendaCursos;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class EnfileirarPendenciasCursoAction
{
    public function execute(AgendaCursos $agenda, string $data): void
    {
        DB::transaction(function () use ($agenda, $data) {
            $registro = AgendaCursos::query()->whereKey($agenda->id)->lockForUpdate()->first();
            if (! $registro
                || $registro->data_inicio?->toDateString() !== $data
                || $registro->status !== 'CONFIRMADO'
                || $registro->tipo_agendamento !== 'ONLINE'
                || $registro->pendencias_enviadas_em) {
                return;
            }
            Mail::to('administrativo@redemetrologica.com.br')->queue(
                (new AvisoPendenciasTreinamentoMail(
                    $registro,
                    $data,
                ))->onConnection('database')->beforeCommit(),
            );
        });
    }
}

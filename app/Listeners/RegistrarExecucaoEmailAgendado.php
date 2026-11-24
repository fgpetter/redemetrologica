<?php

namespace App\Listeners;

use App\Support\ProcessosAgendados;
use Illuminate\Queue\Events\JobAttempted;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Events\JobTimedOut;
use Illuminate\Queue\Jobs\DatabaseJob;
use Illuminate\Support\Facades\Context;

class RegistrarExecucaoEmailAgendado
{
    public function handle(JobProcessing|JobAttempted|JobTimedOut $evento): void
    {
        $job = $evento->job;

        if ($evento->connectionName !== 'database' || ! $job instanceof DatabaseJob
            || ! Context::getHidden(ProcessosAgendados::CONTEXTO_EMAIL)) {
            return;
        }

        $contexto = [
            'job_uuid' => $job->uuid(),
            'job' => $job->resolveName(),
            'job_id' => $job->getJobId(),
            'tentativa' => $job->attempts(),
            'available_at' => (int) $job->getJobRecord()->available_at,
        ];

        if ($evento instanceof JobProcessing) {
            $inicio = now()->getTimestamp();
            ProcessosAgendados::log('email.job.inicio', [
                ...$contexto,
                'inicio_em' => $inicio,
                'atraso_segundos' => $inicio - $contexto['available_at'],
            ]);

            return;
        }

        if ($evento instanceof JobTimedOut) {
            ProcessosAgendados::log('email.job.fim', [
                ...$contexto,
                'resultado' => 'timeout',
                'limite_segundos' => $evento->timeout,
            ], 'warning');

            return;
        }

        $resultado = match (true) {
            $job->hasFailed() => 'falha',
            $job->isReleased() => 'retentativa',
            ! $evento->successful() => 'erro',
            default => 'concluido',
        };

        ProcessosAgendados::log('email.job.fim', [
            ...$contexto,
            'resultado' => $resultado,
            'duracao_ms' => $evento->duration,
            'exception_class' => $evento->exception !== null ? $evento->exception::class : null,
        ], $resultado === 'concluido' ? 'info' : 'warning');
    }
}

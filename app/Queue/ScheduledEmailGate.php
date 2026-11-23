<?php

namespace App\Queue;

use App\Support\ProcessosAgendados;
use Closure;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Queue\Events\JobAttempted;
use Illuminate\Queue\Events\JobQueued;
use Illuminate\Queue\Events\JobTimedOut;
use Illuminate\Queue\Jobs\DatabaseJob;
use Illuminate\Queue\Jobs\JobName;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\Carbon;
use RuntimeException;

class ScheduledEmailGate
{
    public const CONTEXTO_PAYLOAD = 'processo_agendado';

    private const MICROS_POR_SEGUNDO = 1_000_000;

    private const INTERVALO_MICROS = 5 * self::MICROS_POR_SEGUNDO;

    private ?Connection $database = null;

    private ?DatabaseJob $job = null;

    private ?int $aceitoEm = null;

    public function enfileirado(JobQueued $evento, QueueManager $manager): void
    {
        if ($evento->connectionName !== 'database' || $evento->queue !== ProcessosAgendados::FILA_EMAIL) {
            return;
        }

        $queue = $manager->connection($evento->connectionName);

        if (! $queue instanceof ScheduledEmailDatabaseQueue) {
            return;
        }

        $contexto = $this->contexto($evento->payload(), $evento->queue, $evento->id);
        $registrar = static fn () => ProcessosAgendados::log('email.enfileirado', $contexto);
        $database = $queue->getDatabase();

        if ($database->transactionLevel() > 0) {
            $database->afterCommit($registrar);
        } else {
            $registrar();
        }
    }

    /** @param Closure(): ?DatabaseJob $pop */
    public function reservar(Connection $database, Closure $pop, int $retryAfter): ?DatabaseJob
    {
        $job = $database->transaction(function () use ($database, $pop, $retryAfter): ?DatabaseJob {
            $controle = $this->controle($database)->lockForUpdate()->first();

            if ($controle === null) {
                throw new RuntimeException('O controle da fila de e-mails agendados não foi inicializado.');
            }

            if ($controle->disponivel_em > $this->emMicros(Carbon::now())) {
                return null;
            }

            $job = $pop();

            if ($job !== null) {
                // Se o worker morrer, retome só após a expiração da reserva e do intervalo.
                $this->controle($database)->update([
                    'disponivel_em' => $this->emMicros(Carbon::now()) + $retryAfter * self::MICROS_POR_SEGUNDO + self::INTERVALO_MICROS,
                    'job_id' => $job->getJobId(),
                    'tentativa' => $job->attempts(),
                ]);
            }

            return $job;
        }, attempts: 3);

        if ($job !== null) {
            $this->database = $database;
            $this->job = $job;
            $this->aceitoEm = null;
        }

        return $job;
    }

    public function aceitar(): void
    {
        if ($this->job === null) {
            return;
        }

        $aceitoEm = Carbon::now('UTC');
        $this->aceitoEm = $this->emMicros($aceitoEm);

        ProcessosAgendados::log('email.aceito', [
            ...$this->contextoDaTentativa($this->job),
            'aceito_em' => $aceitoEm->format('Y-m-d\TH:i:s.u\Z'),
        ]);
    }

    public function concluir(JobAttempted|JobTimedOut $evento): void
    {
        if ($this->database === null || $this->job !== $evento->job) {
            return;
        }

        $job = $this->job;
        $timeout = $evento instanceof JobTimedOut;
        $falha = $timeout || ! $evento->successful() || $job->isReleased();
        $ate = $falha ? $this->emMicros(Carbon::now()) : $this->aceitoEm;

        $this->controle($this->database)
            ->where('job_id', $job->getJobId())
            ->where('tentativa', $job->attempts())
            ->update([
                'disponivel_em' => $ate === null ? 0 : $ate + self::INTERVALO_MICROS,
                'job_id' => null,
                'tentativa' => null,
            ]);

        $this->database = null;
        $this->job = null;
        $this->aceitoEm = null;

        if ($falha) {
            $excecao = $timeout ? null : $evento->exception;

            ProcessosAgendados::log($job->hasFailed() ? 'email.falhou' : 'email.retentativa', [
                ...$this->contextoDaTentativa($job),
                'timeout' => $timeout,
                'excecao' => $excecao === null ? null : $excecao::class,
            ], $job->hasFailed() ? 'error' : 'warning');
        } elseif ($ate === null) {
            ProcessosAgendados::log('email.ignorado', $this->contextoDaTentativa($job));
        }
    }

    private function controle(Connection $database): Builder
    {
        return $database->table('controle_fila_emails_agendados')->where('id', 1);
    }

    private function contexto(array $payload, string $fila, string|int|null $jobId): array
    {
        return [
            ...($payload[self::CONTEXTO_PAYLOAD] ?? []),
            'fila' => $fila,
            'job' => JobName::resolve($payload['job'], $payload),
            'job_id' => $jobId,
            'job_uuid' => $payload['uuid'] ?? null,
        ];
    }

    private function contextoDaTentativa(DatabaseJob $job): array
    {
        return [
            ...$this->contexto($job->payload(), $job->getQueue(), $job->getJobId()),
            'tentativa' => $job->attempts(),
        ];
    }

    private function emMicros(Carbon $horario): int
    {
        return (int) $horario->format('Uu');
    }
}

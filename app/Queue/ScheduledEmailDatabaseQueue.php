<?php

namespace App\Queue;

use App\Support\ProcessosAgendados;
use Illuminate\Queue\DatabaseQueue;
use Illuminate\Queue\Jobs\DatabaseJob;

class ScheduledEmailDatabaseQueue extends DatabaseQueue
{
    public function pop($queue = null): ?DatabaseJob
    {
        if ($this->getQueue($queue) !== ProcessosAgendados::FILA_EMAIL) {
            return parent::pop($queue);
        }

        return $this->container->make(ScheduledEmailGate::class)->reservar(
            $this->getDatabase(),
            fn () => parent::pop($queue),
            $this->retryAfter,
        );
    }

    protected function withCreatePayloadHooks($queue, array $payload): array
    {
        $payload = parent::withCreatePayloadHooks($queue, $payload);

        if ($queue === ProcessosAgendados::FILA_EMAIL) {
            $payload[ScheduledEmailGate::CONTEXTO_PAYLOAD] = ProcessosAgendados::contexto();
        }

        return $payload;
    }
}

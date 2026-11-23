<?php

namespace App\Jobs;

use App\Support\EnviarEmailAgendado;
use App\Support\ProcessosAgendados;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Mail\Mailable;

class EnviarEmailAgendadoJob implements ShouldQueue
{
    use Queueable;

    public ?int $tries;

    public ?int $timeout;

    public function __construct(
        public Mailable $email,
        public string|array $destinatario,
        public array $contexto = [],
        public string|array|null $cc = null,
    ) {
        $this->tries = $email->tries ?? null;
        $this->timeout = $email->timeout ?? null;
        $this->onConnection('database')->onQueue(ProcessosAgendados::FILA_EMAIL);
    }

    public function handle(EnviarEmailAgendado $envio): void
    {
        ProcessosAgendados::executar($this->contexto, fn () => $envio->execute($this->email, $this->destinatario, $this->cc));
    }
}

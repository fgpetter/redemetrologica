<?php

namespace App\Jobs;

use App\Mail\Fr98Mail;
use App\Models\AgendaCursos;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class EnviarFr98Job implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public int $timeout = 60;

    /**
     * @param  array<string, mixed>  $snapshot
     */
    public function __construct(
        public int $agendaCursoId,
        public string $tentativaId,
        public array $snapshot,
    ) {}

    public function handle(): void
    {
        $enviado = DB::transaction(function (): bool {
            $agenda = AgendaCursos::query()->lockForUpdate()->find($this->agendaCursoId);

            if (! $agenda || $agenda->fr98_envio_id !== $this->tentativaId) {
                return false;
            }

            Mail::to($this->snapshot['destinatario'])->sendNow(new Fr98Mail($this->snapshot));

            $agenda->update([
                'fr98_enviado_em' => now('America/Sao_Paulo'),
                'fr98_envio_id' => null,
            ]);

            return true;
        });

        if ($enviado) {
            Log::channel('mailsent')->info('FR98 aceito pelo serviço de e-mail.', [
                'agenda_curso_id' => $this->agendaCursoId,
                'tentativa_id' => $this->tentativaId,
            ]);
        }
    }

    public function failed(?Throwable $exception): void
    {
        DB::transaction(function (): void {
            $agenda = AgendaCursos::query()->lockForUpdate()->find($this->agendaCursoId);

            if ($agenda && $agenda->fr98_envio_id === $this->tentativaId) {
                $agenda->update(['fr98_envio_id' => null]);
            }
        });

        Log::error('Falha definitiva no envio do FR98.', [
            'agenda_curso_id' => $this->agendaCursoId,
            'tentativa_id' => $this->tentativaId,
            'exception' => $exception,
        ]);
    }
}

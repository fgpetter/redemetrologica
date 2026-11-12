<?php

namespace App\Jobs;

use App\Actions\CoordenarCacheEmailsCursos;
use App\Mail\AvisoPendenciasTreinamentoMail;
use App\Models\AgendaCursos;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Throwable;

class EnviarPendenciasCursoJob implements ShouldQueue
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
        public string $dataEsperada,
        public array $snapshot,
    ) {}

    public function handle(): void
    {
        $enviado = DB::transaction(function (): bool {
            $agenda = AgendaCursos::query()->lockForUpdate()->find($this->agendaCursoId);

            if (! $agenda || $agenda->pendencias_envio_id !== $this->tentativaId) {
                return false;
            }

            if ($agenda->pendencias_enviadas_em !== null || ! $this->elegivel($agenda)) {
                $agenda->update(['pendencias_envio_id' => null]);

                return false;
            }

            $this->validarDestinatario();

            Mail::to($this->snapshot['destinatario'])
                ->sendNow(new AvisoPendenciasTreinamentoMail($this->snapshot));

            $agenda->update([
                'pendencias_enviadas_em' => now('America/Sao_Paulo'),
                'pendencias_envio_id' => null,
            ]);

            return true;
        });

        if ($enviado) {
            Log::channel('mailsent')->info('E-mail automático de pendências de curso aceito pelo serviço de e-mail.', [
                'tipo' => 'pendencias',
                'agenda_curso_id' => $this->agendaCursoId,
                'inscrito_id' => null,
                'tentativa_id' => $this->tentativaId,
            ]);
        }
    }

    public function failed(?Throwable $exception): void
    {
        $vigente = DB::transaction(function (): bool {
            $agenda = AgendaCursos::query()->lockForUpdate()->find($this->agendaCursoId);

            if (! $agenda || $agenda->pendencias_envio_id !== $this->tentativaId) {
                return false;
            }

            $agenda->update(['pendencias_envio_id' => null]);

            return true;
        });

        if ($vigente) {
            app(CoordenarCacheEmailsCursos::class)->invalidar();
        }

        Log::error('Falha definitiva no envio de e-mail automático de curso.', [
            'tipo' => 'pendencias',
            'agenda_curso_id' => $this->agendaCursoId,
            'inscrito_id' => null,
            'tentativa_id' => $this->tentativaId,
            'pendencia_liberada' => $vigente,
            'exception' => $exception,
        ]);
    }

    private function elegivel(AgendaCursos $agenda): bool
    {
        return $agenda->status === 'CONFIRMADO'
            && $agenda->tipo_agendamento === 'ONLINE'
            && $agenda->data_inicio?->toDateString() === $this->dataEsperada
            && $agenda->informacoes_enviadas_em === null
            && (blank($agenda->link_aula) || ! $agenda->materiaisTreinamento()->exists());
    }

    private function validarDestinatario(): void
    {
        Validator::make(
            ['email' => $this->snapshot['destinatario'] ?? null],
            ['email' => ['required', 'email:rfc']],
        )->validate();
    }
}

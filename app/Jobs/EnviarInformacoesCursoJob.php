<?php

namespace App\Jobs;

use App\Actions\CoordenarCacheEmailsCursos;
use App\Mail\InformacoesTreinamentoMail;
use App\Models\AgendaCursos;
use App\Models\CursoInscrito;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Throwable;

class EnviarInformacoesCursoJob implements ShouldQueue
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
        public int $inscritoId,
        public string $tentativaId,
        public string $dataEsperada,
        public array $snapshot,
    ) {}

    public function handle(): void
    {
        $enviado = DB::transaction(function (): bool {
            $agenda = AgendaCursos::query()->lockForUpdate()->find($this->agendaCursoId);

            $inscrito = CursoInscrito::query()
                ->whereKey($this->inscritoId)
                ->lockForUpdate()
                ->first();

            if (! $inscrito || $inscrito->informacoes_envio_id !== $this->tentativaId) {
                return false;
            }

            if (! $agenda
                || $inscrito->agenda_curso_id !== $agenda->getKey()
                || $agenda->informacoes_enviadas_em !== null
                || $inscrito->informacoes_enviadas_em !== null
                || ! $this->elegivel($agenda)) {
                $inscrito->update(['informacoes_envio_id' => null]);

                return false;
            }

            $this->validarDestinatario();

            Mail::to($this->snapshot['destinatario'])
                ->sendNow(new InformacoesTreinamentoMail($this->snapshot));

            $inscrito->update([
                'informacoes_enviadas_em' => now('America/Sao_Paulo'),
                'informacoes_envio_id' => null,
            ]);

            $agenda->sincronizarConfirmacaoInformacoes();

            return true;
        });

        if ($enviado) {
            Log::channel('mailsent')->info('E-mail automático de informações de curso aceito pelo serviço de e-mail.', [
                'tipo' => 'participante',
                'agenda_curso_id' => $this->agendaCursoId,
                'inscrito_id' => $this->inscritoId,
                'tentativa_id' => $this->tentativaId,
            ]);
        }
    }

    public function failed(?Throwable $exception): void
    {
        $vigente = DB::transaction(function (): bool {
            AgendaCursos::query()->lockForUpdate()->find($this->agendaCursoId);

            $inscrito = CursoInscrito::query()
                ->whereKey($this->inscritoId)
                ->lockForUpdate()
                ->first();

            if (! $inscrito || $inscrito->informacoes_envio_id !== $this->tentativaId) {
                return false;
            }

            $inscrito->update(['informacoes_envio_id' => null]);

            return true;
        });

        if ($vigente) {
            app(CoordenarCacheEmailsCursos::class)->invalidar();
        }

        Log::error('Falha definitiva no envio de e-mail automático de curso.', [
            'tipo' => 'participante',
            'agenda_curso_id' => $this->agendaCursoId,
            'inscrito_id' => $this->inscritoId,
            'tentativa_id' => $this->tentativaId,
            'pendencia_liberada' => $vigente,
            'exception' => $exception,
        ]);
    }

    private function elegivel(AgendaCursos $agenda): bool
    {
        return $agenda->status === 'CONFIRMADO'
            && in_array($agenda->tipo_agendamento, ['ONLINE', 'IN-COMPANY'], true)
            && $agenda->data_inicio?->toDateString() === $this->dataEsperada
            && $agenda->informacoes_enviadas_em === null
            && $agenda->pendencias_enviadas_em === null
            && filled($agenda->link_aula)
            && $agenda->materiaisTreinamento()->exists();
    }

    private function validarDestinatario(): void
    {
        Validator::make(
            ['email' => $this->snapshot['destinatario'] ?? null],
            ['email' => ['required', 'email:rfc']],
        )->validate();
    }
}

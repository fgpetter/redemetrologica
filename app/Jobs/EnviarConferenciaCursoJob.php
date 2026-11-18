<?php

namespace App\Jobs;

use App\Actions\CoordenarCacheEmailsCursos;
use App\Mail\AvisoConferenciaTreinamentoMail;
use App\Models\AgendaCursos;
use App\Support\Observabilidade\ResumoExecucao;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Throwable;

class EnviarConferenciaCursoJob implements ShouldQueue
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
        $resumo = new ResumoExecucao('EnviarConferenciaCursoJob', ['agenda_curso_id' => $this->agendaCursoId, 'tentativa_id' => $this->tentativaId, 'tentativa' => $this->attempts()], 'mailsent');
        $resumo->executar(function (ResumoExecucao $resumo): void {
            $resumo->resultado('ignorado', 'tentativa_obsoleta_ou_inelegivel');

            $enviado = $resumo->etapa('validar_enviar_confirmar', fn () => DB::transaction(function () use ($resumo): bool {
                $agenda = AgendaCursos::query()->lockForUpdate()->find($this->agendaCursoId);

                if (! $agenda || $agenda->conferencia_envio_id !== $this->tentativaId) {
                    return false;
                }

                if ($agenda->conferencia_enviada_em !== null || ! $this->elegivel($agenda)) {
                    $resumo->etapa('confirmar_estado', fn () => $agenda->update(['conferencia_envio_id' => null]));

                    return false;
                }

                $this->validarDestinatario();

                $resumo->etapa('enviar', fn () => Mail::to($this->snapshot['destinatario'])
                    ->sendNow(new AvisoConferenciaTreinamentoMail($this->snapshot)));

                $resumo->etapa('confirmar_estado', fn () => $agenda->update([
                    'conferencia_enviada_em' => now('America/Sao_Paulo'),
                    'conferencia_envio_id' => null,
                ]));

                return true;
            }));

            if ($enviado) {
                $resumo->contexto([
                    'tipo' => 'conferencia',
                    'agenda_curso_id' => $this->agendaCursoId,
                    'inscrito_id' => null,
                    'tentativa_id' => $this->tentativaId,
                ]);
                $resumo->resultado('aceito_transporte');
            }
        }, job: true);
    }

    public function failed(?Throwable $exception): void
    {
        $vigente = DB::transaction(function (): bool {
            $agenda = AgendaCursos::query()->lockForUpdate()->find($this->agendaCursoId);

            if (! $agenda || $agenda->conferencia_envio_id !== $this->tentativaId) {
                return false;
            }

            $agenda->update(['conferencia_envio_id' => null]);

            return true;
        });

        if ($vigente) {
            app(CoordenarCacheEmailsCursos::class)->invalidar();
        }

        Log::error('Falha definitiva no envio de e-mail automático de curso.', [
            'tipo' => 'conferencia',
            'agenda_curso_id' => $this->agendaCursoId,
            'inscrito_id' => null,
            'tentativa_id' => $this->tentativaId,
            'pendencia_liberada' => $vigente,
            'evento' => 'comunicacao.falha_definitiva',
            'resultado' => 'falha_definitiva',
        ]);
    }

    private function elegivel(AgendaCursos $agenda): bool
    {
        return $agenda->status === 'CONFIRMADO'
            && in_array($agenda->tipo_agendamento, ['ONLINE', 'IN-COMPANY'], true)
            && $agenda->data_inicio?->toDateString() === $this->dataEsperada;
    }

    private function validarDestinatario(): void
    {
        Validator::make(
            ['email' => $this->snapshot['destinatario'] ?? null],
            ['email' => ['required', 'email:rfc']],
        )->validate();
    }
}

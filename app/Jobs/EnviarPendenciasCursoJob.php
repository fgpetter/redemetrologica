<?php

namespace App\Jobs;

use App\Actions\CoordenarCacheEmailsCursos;
use App\Mail\AvisoPendenciasTreinamentoMail;
use App\Models\AgendaCursos;
use App\Support\Observabilidade\ResumoExecucao;
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
        $resumo = new ResumoExecucao('EnviarPendenciasCursoJob', ['agenda_curso_id' => $this->agendaCursoId, 'tentativa_id' => $this->tentativaId, 'tentativa' => $this->attempts()], 'mailsent');
        $resumo->executar(function (ResumoExecucao $resumo): void {
            $resumo->resultado('ignorado', 'tentativa_obsoleta_ou_inelegivel');

            $enviado = DB::transaction(function () use ($resumo): bool {
                $agenda = $resumo->etapa('carregar_registros', fn () => AgendaCursos::query()->lockForUpdate()->find($this->agendaCursoId));

                if ($resumo->etapa('validar_elegibilidade', fn () => ! $agenda || $agenda->pendencias_envio_id !== $this->tentativaId)) {
                    $resumo->resultadoEtapa('validar_elegibilidade', 'ignorado');

                    return false;
                }

                if ($resumo->etapa('validar_elegibilidade', fn () => $agenda->pendencias_enviadas_em !== null || ! $this->elegivel($agenda))) {
                    $resumo->resultadoEtapa('validar_elegibilidade', 'ignorado');

                    $resumo->etapa('confirmar_estado', fn () => $agenda->update(['pendencias_envio_id' => null]));

                    return false;
                }

                $resumo->etapa('validar_destinatario', fn () => $this->validarDestinatario());

                $resumo->etapa('enviar', fn () => Mail::to($this->snapshot['destinatario'])
                    ->sendNow(new AvisoPendenciasTreinamentoMail($this->snapshot)));

                $resumo->etapa('confirmar_estado', fn () => $agenda->update([
                    'pendencias_enviadas_em' => now('America/Sao_Paulo'),
                    'pendencias_envio_id' => null,
                ]));

                return true;
            });

            if ($enviado) {
                $resumo->contexto([
                    'tipo' => 'pendencias',
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
            'evento' => 'comunicacao.falha_definitiva',
            'resultado' => 'falha_definitiva',
        ]);
    }

    private function elegivel(AgendaCursos $agenda): bool
    {
        return $agenda->status === 'CONFIRMADO'
            && in_array($agenda->tipo_agendamento, ['ONLINE', 'IN-COMPANY'], true)
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

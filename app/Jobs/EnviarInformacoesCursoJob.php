<?php

namespace App\Jobs;

use App\Actions\CoordenarCacheEmailsCursos;
use App\Mail\InformacoesTreinamentoMail;
use App\Models\AgendaCursos;
use App\Models\CursoInscrito;
use App\Support\Observabilidade\ResumoExecucao;
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
        $resumo = new ResumoExecucao('EnviarInformacoesCursoJob', ['agenda_curso_id' => $this->agendaCursoId, 'inscrito_id' => $this->inscritoId, 'tentativa_id' => $this->tentativaId, 'tentativa' => $this->attempts()], 'mailsent');
        $resumo->executar(function (ResumoExecucao $resumo): void {
            $resumo->resultado('ignorado', 'tentativa_obsoleta_ou_inelegivel');

            $enviado = DB::transaction(function () use ($resumo): bool {
                $agenda = $resumo->etapa('carregar_registros', fn () => AgendaCursos::query()->lockForUpdate()->find($this->agendaCursoId));

                $inscrito = $resumo->etapa('carregar_registros', fn () => CursoInscrito::query()
                    ->whereKey($this->inscritoId)
                    ->lockForUpdate()
                    ->first());

                if ($resumo->etapa('validar_elegibilidade', fn () => ! $inscrito || $inscrito->informacoes_envio_id !== $this->tentativaId)) {
                    $resumo->resultadoEtapa('validar_elegibilidade', 'ignorado');

                    return false;
                }

                if ($resumo->etapa('validar_elegibilidade', fn () => ! $agenda
                    || $inscrito->agenda_curso_id !== $agenda->getKey()
                    || $agenda->informacoes_enviadas_em !== null
                    || $inscrito->informacoes_enviadas_em !== null
                    || ! $this->elegivel($agenda))) {
                    $resumo->resultadoEtapa('validar_elegibilidade', 'ignorado');

                    $resumo->etapa('confirmar_estado', fn () => $inscrito->update(['informacoes_envio_id' => null]));

                    return false;
                }

                $resumo->etapa('validar_destinatario', fn () => $this->validarDestinatario());

                $resumo->etapa('enviar', fn () => Mail::to($this->snapshot['destinatario'])
                    ->sendNow(new InformacoesTreinamentoMail($this->snapshot)));

                $resumo->etapa('confirmar_estado', fn () => $inscrito->update([
                    'informacoes_enviadas_em' => now('America/Sao_Paulo'),
                    'informacoes_envio_id' => null,
                ]));

                $resumo->etapa('sincronizar_agenda', fn () => $agenda->sincronizarConfirmacaoInformacoes());

                return true;
            });

            if ($enviado) {
                $resumo->contexto([
                    'tipo' => 'participante',
                    'agenda_curso_id' => $this->agendaCursoId,
                    'inscrito_id' => $this->inscritoId,
                    'tentativa_id' => $this->tentativaId,
                ]);
                $resumo->resultado('aceito_transporte');
            }
        }, job: true);
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

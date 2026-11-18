<?php

namespace App\Jobs;

use App\Console\Commands\EnviarCertificadoInterlabPendenteCommand;
use App\Mail\CertificadoInterlabMail;
use App\Models\DadosGeraDoc;
use App\Models\InterlabInscrito;
use App\Support\Observabilidade\ResumoExecucao;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use RuntimeException;
use Throwable;

class EnviarCertificadoInterlabPendenteJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public int $timeout = 60;

    public function __construct(
        public int $inscritoId,
        public int $dadosDocId,
        public string $tentativaId,
        public string $to,
        public ?string $cc = null,
    ) {}

    public function handle(): void
    {
        $resumo = new ResumoExecucao('EnviarCertificadoInterlabPendenteJob', ['inscrito_id' => $this->inscritoId, 'documento_id' => $this->dadosDocId, 'tentativa_id' => $this->tentativaId, 'tentativa' => $this->attempts()], 'mailsent');
        $resumo->executar(function (ResumoExecucao $resumo): void {
            $resumo->resultado('ignorado', 'tentativa_obsoleta_ou_inelegivel');

            $agendaId = $resumo->etapa('validar_enviar_confirmar', fn () => DB::transaction(function () use ($resumo): ?int {
                $inscrito = InterlabInscrito::query()->lockForUpdate()->find($this->inscritoId);

                if (! $inscrito || $inscrito->certificado_envio_id !== $this->tentativaId) {
                    return null;
                }

                if (! $inscrito->agendaInterlab()->where('status', 'CONCLUIDO')->exists()
                    || ! $inscrito->lancamentoFinanceiro()->where('status', 'EFETIVADO')->exists()) {
                    throw new RuntimeException('A inscrição não possui mais agenda concluída e pagamento efetivado.');
                }

                Validator::make(['to' => $this->to, 'cc' => $this->cc], [
                    'to' => ['required', 'email:rfc'],
                    'cc' => ['nullable', 'email:rfc'],
                ])->validate();

                $mail = Mail::to($this->to);

                if ($this->cc !== null && strcasecmp($this->to, $this->cc) !== 0) {
                    $mail->cc($this->cc);
                }

                $resumo->etapa('enviar', fn () => $mail->sendNow(new CertificadoInterlabMail(DadosGeraDoc::findOrFail($this->dadosDocId))));
                $resumo->etapa('confirmar_estado', fn () => $inscrito->update(['certificado_envio_id' => null]));

                return $inscrito->agenda_interlab_id;
            }));

            if ($agendaId !== null) {
                $resumo->contexto([
                    'tipo' => 'certificado_interlab',
                    'agenda_id' => $agendaId,
                    'inscrito_id' => $this->inscritoId,
                    'documento_id' => $this->dadosDocId,
                    'tentativa_id' => $this->tentativaId,
                ]);
                $resumo->resultado('aceito_transporte');
            }
        }, job: true);
    }

    public function failed(?Throwable $exception): void
    {
        $agendaId = DB::transaction(function (): ?int {
            $inscrito = InterlabInscrito::query()
                ->where('certificado_envio_id', $this->tentativaId)
                ->lockForUpdate()->find($this->inscritoId);

            if (! $inscrito) {
                return null;
            }

            $inscrito->update(['certificado_emitido' => null, 'certificado_envio_id' => null]);

            return $inscrito->agenda_interlab_id;
        });

        if ($agendaId !== null) {
            Cache::forget(EnviarCertificadoInterlabPendenteCommand::SEM_CERTIFICADO_PENDENTE_CACHE_KEY);
        }

        Log::channel('certificados_falha')->error('Falha definitiva no envio de certificado Interlab.', [
            'tipo' => 'certificado_interlab',
            'agenda_id' => $agendaId,
            'inscrito_id' => $this->inscritoId,
            'documento_id' => $this->dadosDocId,
            'tentativa_id' => $this->tentativaId,
            'pendencia_liberada' => $agendaId !== null,
            'evento' => 'comunicacao.falha_definitiva',
            'resultado' => 'falha_definitiva',
        ]);
    }
}

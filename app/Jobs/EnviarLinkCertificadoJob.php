<?php

namespace App\Jobs;

use App\Console\Commands\EnviarCertificadoPendenteCommand;
use App\Mail\CertificadoNotification;
use App\Models\CursoInscrito;
use App\Models\DadosGeraDoc;
use App\Support\Observabilidade\ResumoExecucao;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Throwable;

class EnviarLinkCertificadoJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public int $timeout = 60;

    public function __construct(
        public int $dadosDocId,
        public int $inscritoId,
        public string $tentativaId,
        public string $to,
    ) {}

    public function handle(): void
    {
        $resumo = new ResumoExecucao('EnviarLinkCertificadoJob', ['inscrito_id' => $this->inscritoId, 'documento_id' => $this->dadosDocId, 'tentativa_id' => $this->tentativaId, 'tentativa' => $this->attempts()], 'mailsent');
        $resumo->executar(function (ResumoExecucao $resumo): void {
            $resumo->resultado('ignorado', 'tentativa_obsoleta_ou_inelegivel');

            $resultado = DB::transaction(function () use ($resumo): ?array {
                $inscrito = $resumo->etapa('carregar_registros', fn () => CursoInscrito::query()->lockForUpdate()->find($this->inscritoId));

                if ($resumo->etapa('validar_elegibilidade', fn () => ! $inscrito || $inscrito->certificado_envio_id !== $this->tentativaId)) {
                    $resumo->resultadoEtapa('validar_elegibilidade', 'ignorado');

                    return null;
                }

                $resumo->etapa('validar_destinatario', fn () => Validator::make(['to' => $this->to], ['to' => ['required', 'email:rfc']])->validate());

                $dadosDoc = $resumo->etapa('carregar_documento', fn () => DadosGeraDoc::findOrFail($this->dadosDocId));
                $resumo->etapa('enviar', fn () => Mail::to($this->to)->sendNow(new CertificadoNotification($dadosDoc)));
                $resumo->etapa('confirmar_estado', fn () => $inscrito->update(['certificado_envio_id' => null]));

                return [
                    'agenda_id' => $inscrito->agenda_curso_id,
                    'inscrito_id' => $inscrito->id,
                ];
            });

            if ($resultado !== null) {
                $resumo->contexto([
                    'tipo' => 'certificado_curso',
                    'agenda_id' => $resultado['agenda_id'],
                    'inscrito_id' => $resultado['inscrito_id'],
                    'documento_id' => $this->dadosDocId,
                    'tentativa_id' => $this->tentativaId,
                ]);
                $resumo->resultado('aceito_transporte');
            }
        }, job: true);
    }

    public function failed(?Throwable $exception): void
    {
        $resultado = DB::transaction(function (): ?array {
            $inscrito = CursoInscrito::query()
                ->where('certificado_envio_id', $this->tentativaId)
                ->lockForUpdate()->find($this->inscritoId);

            if (! $inscrito) {
                return null;
            }

            $inscrito->update(['certificado_emitido' => null, 'certificado_envio_id' => null]);

            return [
                'agenda_id' => $inscrito->agenda_curso_id,
                'inscrito_id' => $inscrito->id,
            ];
        });

        if ($resultado !== null) {
            Cache::forget(EnviarCertificadoPendenteCommand::SEM_CERTIFICADO_PENDENTE_CACHE_KEY);
        }

        Log::channel('certificados_falha')->error('Falha definitiva no envio de certificado de curso.', [
            'tipo' => 'certificado_curso',
            'agenda_id' => $resultado['agenda_id'] ?? null,
            'inscrito_id' => $resultado['inscrito_id'] ?? $this->inscritoId,
            'documento_id' => $this->dadosDocId,
            'tentativa_id' => $this->tentativaId,
            'pendencia_liberada' => $resultado !== null,
            'evento' => 'comunicacao.falha_definitiva',
            'resultado' => 'falha_definitiva',
        ]);
    }
}

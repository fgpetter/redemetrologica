<?php

namespace App\Jobs;

use App\Mail\CertificadoNotification;
use App\Models\CursoInscrito;
use App\Models\DadosGeraDoc;
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
        $resultado = DB::transaction(function (): ?array {
            $inscrito = CursoInscrito::query()->lockForUpdate()->find($this->inscritoId);

            if (! $inscrito || $inscrito->certificado_envio_id !== $this->tentativaId) {
                return null;
            }

            Validator::make(['to' => $this->to], ['to' => ['required', 'email:rfc']])->validate();

            Mail::to($this->to)->sendNow(new CertificadoNotification(DadosGeraDoc::findOrFail($this->dadosDocId)));
            $inscrito->update(['certificado_envio_id' => null]);

            return [
                'agenda_id' => $inscrito->agenda_curso_id,
                'inscrito_id' => $inscrito->id,
            ];
        });

        if ($resultado !== null) {
            Log::channel('mailsent')->info('Certificado de curso aceito pelo serviço de e-mail.', [
                'tipo' => 'certificado_curso',
                'agenda_id' => $resultado['agenda_id'],
                'inscrito_id' => $resultado['inscrito_id'],
                'documento_id' => $this->dadosDocId,
                'tentativa_id' => $this->tentativaId,
            ]);
        }
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
            Cache::forget(\App\Console\Commands\EnviarCertificadoPendenteCommand::SEM_CERTIFICADO_PENDENTE_CACHE_KEY);
        }

        Log::channel('certificados_falha')->error('Falha definitiva no envio de certificado de curso.', [
            'tipo' => 'certificado_curso',
            'agenda_id' => $resultado['agenda_id'] ?? null,
            'inscrito_id' => $resultado['inscrito_id'] ?? $this->inscritoId,
            'documento_id' => $this->dadosDocId,
            'tentativa_id' => $this->tentativaId,
            'pendencia_liberada' => $resultado !== null,
            'exception' => $exception,
        ]);
    }
}

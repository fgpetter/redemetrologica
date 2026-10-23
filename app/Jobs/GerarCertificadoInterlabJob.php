<?php

namespace App\Jobs;

use App\Mail\CertificadoInterlabMail;
use App\Models\DadosGeraDoc;
use App\Models\InterlabInscrito;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Throwable;

class GerarCertificadoInterlabJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public int $timeout = 60;

    public function __construct(public int $dadosDocId) {}

    public function handle(): void
    {
        $dadosDoc = DadosGeraDoc::findOrFail($this->dadosDocId);
        $email = trim((string) ($dadosDoc->content['laboratorio_email'] ?? ''));
        Validator::make(['email' => $email], ['email' => ['required', 'email:rfc']])->validate();

        Mail::to($email)->sendNow(new CertificadoInterlabMail($dadosDoc));

        $inscrito = InterlabInscrito::query()->find($dadosDoc->content['participante_id'] ?? null);

        Log::channel('mailsent')->info('Certificado Interlab aceito pelo serviço de e-mail.', [
            'tipo' => 'certificado_interlab',
            'agenda_id' => $inscrito?->agenda_interlab_id,
            'inscrito_id' => $inscrito?->id,
            'documento_id' => $dadosDoc->id,
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        $dadosDoc = DadosGeraDoc::find($this->dadosDocId);
        $inscrito = InterlabInscrito::query()->find($dadosDoc?->content['participante_id'] ?? null);

        Log::channel('certificados_falha')->error('Falha definitiva no envio de certificado Interlab.', [
            'tipo' => 'certificado_interlab',
            'agenda_id' => $inscrito?->agenda_interlab_id,
            'inscrito_id' => $inscrito?->id ?? $dadosDoc?->content['participante_id'] ?? null,
            'documento_id' => $this->dadosDocId,
            'exception' => $exception,
        ]);
    }
}

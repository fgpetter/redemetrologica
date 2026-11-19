<?php

namespace App\Jobs;

use App\Mail\CertificadoInterlabMail;
use App\Models\DadosGeraDoc;
use App\Models\InterlabInscrito;
use App\Support\Observabilidade\ResumoExecucao;
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
        $resumo = new ResumoExecucao('GerarCertificadoInterlabJob', ['documento_id' => $this->dadosDocId, 'tentativa' => $this->attempts()], 'mailsent');
        $resumo->executar(function (ResumoExecucao $resumo): void {
            $resumo->resultado('ignorado', 'tentativa_obsoleta_ou_inelegivel');

            $dadosDoc = $resumo->etapa('carregar_documento', fn () => DadosGeraDoc::findOrFail($this->dadosDocId));
            $email = $resumo->etapa('validar_destinatario', function () use ($dadosDoc): string {
                $email = trim((string) ($dadosDoc->content['laboratorio_email'] ?? ''));
                Validator::make(['email' => $email], ['email' => ['required', 'email:rfc']])->validate();

                return $email;
            });

            $resumo->etapa('enviar', fn () => Mail::to($email)->sendNow(new CertificadoInterlabMail($dadosDoc)));

            $inscrito = $resumo->etapa('carregar_referencias', fn () => InterlabInscrito::query()->find($dadosDoc->content['participante_id'] ?? null));

            $resumo->contexto([
                'tipo' => 'certificado_interlab',
                'agenda_id' => $inscrito?->agenda_interlab_id,
                'inscrito_id' => $inscrito?->id,
                'documento_id' => $dadosDoc->id,
            ]);
            $resumo->resultado('aceito_transporte');
        }, job: true);
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
            'evento' => 'comunicacao.falha_definitiva',
            'resultado' => 'falha_definitiva',
        ]);
    }
}

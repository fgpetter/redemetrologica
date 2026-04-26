<?php

namespace App\Jobs;

use App\Mail\LinkSenhaInterlabNotification;
use App\Models\DadosGeraDoc;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Envio pontual de link de senha (reenvio por agenda), com destinatário e cópia explícitos.
 * O fluxo normal continua usando {@see EnviarLinkSenhaInterlabJob}.
 */
class ReenviarLinkSenhaInterlabJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public function __construct(
        public int $dadosDocId,
        public string $emailDestinatario,
        public ?string $emailCopia = null,
    ) {}

    public function handle(): void
    {
        try {
            if (blank($this->emailDestinatario)) {
                Log::warning('Reenvio de senha interlab ignorado por falta de destinatario', [
                    'dados_doc_id' => $this->dadosDocId,
                ]);

                return;
            }

            $dadosDoc = DadosGeraDoc::findOrFail($this->dadosDocId);

            $mailer = Mail::to($this->emailDestinatario);

            if (filled($this->emailCopia)) {
                $mailer->cc($this->emailCopia);
            }

            $mailer->queue(new LinkSenhaInterlabNotification($dadosDoc));
        } catch (\Exception $e) {
            report($e);
        }
    }
}

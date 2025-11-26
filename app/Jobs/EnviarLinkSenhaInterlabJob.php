<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\DadosGeraDoc;
use App\Mail\LinkSenhaInterlabNotification;

class EnviarLinkSenhaInterlabJob implements ShouldQueue
{
    use Queueable;

    public $dadosDocId;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct($dadosDocId)
    {
        $this->dadosDocId = $dadosDocId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $dadosDoc = DadosGeraDoc::findOrFail($this->dadosDocId);

            Mail::mailer('interlaboratorial')
                ->to($dadosDoc->content['laboratorio_email'])
                ->cc('sistema@redemetrologica.com.br')
                ->queue(new LinkSenhaInterlabNotification($dadosDoc));

        } catch (\Exception $e) {
            Log::error('Falha ao enviar link senha interlab para DadosGeraDoc ID: ' . $this->dadosDocId, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }
}

<?php

namespace App\Jobs;

use App\Models\DadosGeraDoc;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Queue\Queueable;
use App\Mail\LinkSenhaInterlabNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

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

            Mail::to($dadosDoc->content['laboratorio_email'])
                ->cc('sistema@redemetrologica.com.br')
                ->queue(new LinkSenhaInterlabNotification($dadosDoc));

        } catch (\Exception $e) {
            report($e);
        }
    }
}

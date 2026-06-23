<?php

namespace App\Jobs;

use App\Mail\LinkSenhaInterlabNotification;
use App\Models\DadosGeraDoc;
use App\Models\InterlabInscrito;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EnviaSenhaPepJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    /**
     * @param  array<int, string>  $destinatarios
     */
    public function __construct(
        public int $dadosGeraDocId,
        public array $destinatarios,
        public int $inscritoId,
    ) {}

    public function handle(): void
    {
        $dadosDoc = DadosGeraDoc::query()->find($this->dadosGeraDocId);

        if (! $dadosDoc) {
            Log::warning('EnviaSenhaPepJob: DadosGeraDoc não encontrado.', [
                'dados_gera_doc_id' => $this->dadosGeraDocId,
                'inscrito_id' => $this->inscritoId,
            ]);

            return;
        }

        Mail::to($this->destinatarios)
            ->cc('sistema@redemetrologica.com.br')
            ->sendNow(new LinkSenhaInterlabNotification($dadosDoc));

        InterlabInscrito::query()
            ->whereKey($this->inscritoId)
            ->update(['senha_enviada' => now()]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Falha ao enviar senha PEP para inscrito ID: '.$this->inscritoId, [
            'dados_gera_doc_id' => $this->dadosGeraDocId,
            'error' => $exception->getMessage(),
        ]);
    }
}

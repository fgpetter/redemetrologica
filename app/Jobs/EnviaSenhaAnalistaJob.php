<?php

namespace App\Jobs;

use App\Mail\LinkSenhaAnalistaNotification;
use App\Models\DadosGeraDoc;
use App\Models\InterlabAnalista;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EnviaSenhaAnalistaJob implements ShouldQueue
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
        public int $analistaId,
    ) {}

    public function handle(): void
    {
        $dadosDoc = DadosGeraDoc::query()->find($this->dadosGeraDocId);

        if (! $dadosDoc) {
            Log::warning('EnviaSenhaAnalistaJob: DadosGeraDoc não encontrado.', [
                'dados_gera_doc_id' => $this->dadosGeraDocId,
                'analista_id' => $this->analistaId,
            ]);

            return;
        }

        Mail::to($this->destinatarios)
            ->cc('sistema@redemetrologica.com.br')
            ->sendNow(new LinkSenhaAnalistaNotification($dadosDoc));

        InterlabAnalista::query()
            ->whereKey($this->analistaId)
            ->update(['senha_enviada' => now()]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Falha ao enviar senha de analista ID: '.$this->analistaId, [
            'dados_gera_doc_id' => $this->dadosGeraDocId,
            'error' => $exception->getMessage(),
        ]);
    }
}

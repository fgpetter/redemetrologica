<?php

namespace App\Jobs;

use App\Mail\LinkSenhaAnalistaNotification;
use App\Models\DadosGeraDoc;
use App\Models\InterlabAnalista;
use App\Support\Observabilidade\ResumoExecucao;
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
        $resumo = new ResumoExecucao('EnviaSenhaAnalistaJob', ['analista_id' => $this->analistaId, 'documento_id' => $this->dadosGeraDocId, 'tentativa' => $this->attempts()], 'mailsent');
        $resumo->executar(function (ResumoExecucao $resumo): void {
            $resumo->resultado('ignorado', 'tentativa_obsoleta_ou_inelegivel');

            $dadosDoc = DadosGeraDoc::query()->find($this->dadosGeraDocId);

            if (! $dadosDoc) {
                $resumo->resultado('impedido', 'documento_ausente');

                return;
            }

            $resumo->etapa('enviar', fn () => Mail::to($this->destinatarios)
                ->cc('sistema@redemetrologica.com.br')
                ->sendNow(new LinkSenhaAnalistaNotification($dadosDoc)));

            InterlabAnalista::query()
                ->whereKey($this->analistaId)
                ->update(['senha_enviada' => now()]);
            $resumo->resultado('aceito_transporte');
        }, job: true);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Falha ao enviar senha de analista ID: '.$this->analistaId, [
            'dados_gera_doc_id' => $this->dadosGeraDocId,
            'evento' => 'cartas_senha.falha_definitiva',
            'resultado' => 'falha_definitiva',
        ]);
    }
}

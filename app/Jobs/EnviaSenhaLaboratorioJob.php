<?php

namespace App\Jobs;

use App\Mail\LinkSenhaLaboratorioNotification;
use App\Models\DadosGeraDoc;
use App\Models\InterlabInscrito;
use App\Support\Observabilidade\ResumoExecucao;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EnviaSenhaLaboratorioJob implements ShouldQueue
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
        $resumo = new ResumoExecucao('EnviaSenhaLaboratorioJob', ['inscrito_id' => $this->inscritoId, 'documento_id' => $this->dadosGeraDocId, 'tentativa' => $this->attempts()], 'mailsent');
        $resumo->executar(function (ResumoExecucao $resumo): void {
            $resumo->resultado('ignorado', 'tentativa_obsoleta_ou_inelegivel');

            $dadosDoc = DadosGeraDoc::query()->find($this->dadosGeraDocId);

            if (! $dadosDoc) {
                $resumo->resultado('impedido', 'documento_ausente');

                return;
            }

            $resumo->etapa('enviar', fn () => Mail::to($this->destinatarios)
                ->cc('sistema@redemetrologica.com.br')
                ->sendNow(new LinkSenhaLaboratorioNotification($dadosDoc)));

            InterlabInscrito::query()
                ->whereKey($this->inscritoId)
                ->update(['senha_enviada' => now()]);
            $resumo->resultado('aceito_transporte');
        }, job: true);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Falha ao enviar senha de laboratório para inscrito ID: '.$this->inscritoId, [
            'dados_gera_doc_id' => $this->dadosGeraDocId,
            'evento' => 'cartas_senha.falha_definitiva',
            'resultado' => 'falha_definitiva',
        ]);
    }
}

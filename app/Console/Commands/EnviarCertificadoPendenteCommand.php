<?php

namespace App\Console\Commands;

use App\Actions\EnviarCertificadoAction;
use App\Models\CursoInscrito;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EnviarCertificadoPendenteCommand extends Command
{
    protected $signature = 'certificados:enviar-pendente';

    protected $description = 'Envia certificado para o inscrito em curso mais antigo com pagamento efetivado e sem certificado emitido';

    public function handle(EnviarCertificadoAction $action): void
    {
        $inscrito = CursoInscrito::whereNotNull('lancamento_financeiro_id')
            ->whereHas('lancamentoFinanceiro', fn ($q) => $q->where('status', 'EFETIVADO'))
            ->whereNull('certificado_emitido')
            ->oldest('data_inscricao')
            ->first();

        if (! $inscrito) {
            // Se não há inscritos pendentes, notificar o sistema via email
            Mail::raw(
                'Não há certificados pendentes para envio no momento.',
                function ($message) {
                    $message->to('sistema@redemetrologica.com.br')->subject('Notificação: Sem certificados pendentes');
            }
        );

        $this->info('Nenhum inscrito pendente encontrado. Notificação enviada para o sistema.');
        return;

        }

        $action->execute($inscrito);

        Log::channel('mailsent')->info("Certificado enviado para \"{$inscrito->nome}\" ({$inscrito->email})");
    }
}

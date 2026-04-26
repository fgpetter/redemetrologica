<?php

namespace App\Console\Commands;

use App\Actions\EnviarCertificadoAction;
use App\Models\CursoInscrito;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EnviarCertificadoPendenteCommand extends Command
{
    public const SEM_CERTIFICADO_PENDENTE_CACHE_KEY = 'certificados:sem-pendencia';

    protected $signature = 'certificados:enviar-pendente';

    protected $description = 'Envia certificado para o inscrito em curso mais antigo com pagamento efetivado e sem certificado emitido';

    public function handle(EnviarCertificadoAction $action): void
    {
        $inscrito = CursoInscrito::whereNotNull('lancamento_financeiro_id')
            ->whereHas('lancamentoFinanceiro', fn ($q) => $q->where('status', 'EFETIVADO'))
            ->whereHas('agendaCurso', function ($q) {
                $q->where('status', 'REALIZADO')
                    ->where(function ($agendaQuery) {
                        $agendaQuery->whereNull('tipo_agendamento')
                            ->orWhere('tipo_agendamento', '!=', 'IN-COMPANY');
                    });
            })
            ->whereNull('certificado_emitido')
            ->oldest('data_inscricao')
            ->first();

        if (! $inscrito) {
            Cache::put(self::SEM_CERTIFICADO_PENDENTE_CACHE_KEY, true, now()->addDay()->startOfDay());

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

        Log::channel('mailsent')->info("Certificado de \"{$inscrito->agendaCurso->curso->descricao}\" enviado por agendamento para \"{$inscrito->nome}\" ({$inscrito->email})");
    }
}

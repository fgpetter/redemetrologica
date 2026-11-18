<?php

namespace App\Console\Commands;

use App\Actions\EnfileirarCertificadoInterlabPendenteAction;
use App\Models\InterlabInscrito;
use App\Support\Observabilidade\ResumoExecucao;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Mail;

class EnviarCertificadoInterlabPendenteCommand extends Command
{
    public const SEM_CERTIFICADO_PENDENTE_CACHE_KEY = 'interlab:certificados:sem-pendencia';

    protected $signature = 'interlab:enviar-certificado-pendente';

    protected $description = 'Enfileira um certificado Interlab pendente com pagamento efetivado';

    public function handle(EnfileirarCertificadoInterlabPendenteAction $action): int
    {
        $resumo = new ResumoExecucao('EnviarCertificadoInterlabPendenteCommand', [], 'stack');

        return $resumo->executar(function (ResumoExecucao $resumo) use ($action): int {

            if (Cache::has(self::SEM_CERTIFICADO_PENDENTE_CACHE_KEY)) {
                $resumo->resultado('ignorado', 'cache_sem_pendencias');

                return self::SUCCESS;
            }

            $inscritos = InterlabInscrito::certificadosPendentes()
                ->orderBy('data_inscricao')->orderBy('id')->lazy(100);

            foreach ($inscritos as $inscrito) {
                $item = $resumo->item('EnfileirarCertificadoInterlabPendenteAction', [
                    'tipo' => 'certificado_interlab',
                    'agenda_id' => $inscrito->agenda_interlab_id,
                    'inscrito_id' => $inscrito->id,
                    'inscrito_uid' => $inscrito->uid,
                ]);

                try {
                    if (Context::scope(fn (): bool => $action->execute($inscrito->id, resumo: $item), $item->correlacao())) {
                        $resumo->incrementar('enfileirados');
                        $resumo->resultado('enfileirado');
                        $this->info("Certificado da inscrição {$inscrito->id} enfileirado.");

                        return self::SUCCESS;
                    }
                } finally {
                    $item->encerrar();
                }
            }

            $expiraEm = now('America/Sao_Paulo')->addDay()->startOfDay();

            if (Cache::add(self::SEM_CERTIFICADO_PENDENTE_CACHE_KEY, true, $expiraEm)) {
                Mail::raw('Não há certificados Interlab aptos para envio no momento.', function ($message): void {
                    $message->to('sistema@redemetrologica.com.br')->subject('Notificação: Sem certificados Interlab pendentes');
                });
            }

            $this->info('Nenhuma inscrição apta para envio. Consultas suspensas até a próxima meia-noite.');

            return self::SUCCESS;
        });
    }
}

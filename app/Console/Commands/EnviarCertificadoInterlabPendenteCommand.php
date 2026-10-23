<?php

namespace App\Console\Commands;

use App\Actions\EnfileirarCertificadoInterlabPendenteAction;
use App\Models\InterlabInscrito;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class EnviarCertificadoInterlabPendenteCommand extends Command
{
    public const SEM_CERTIFICADO_PENDENTE_CACHE_KEY = 'interlab:certificados:sem-pendencia';

    protected $signature = 'interlab:enviar-certificado-pendente';

    protected $description = 'Enfileira um certificado Interlab pendente com pagamento efetivado';

    public function handle(EnfileirarCertificadoInterlabPendenteAction $action): int
    {
        if (Cache::has(self::SEM_CERTIFICADO_PENDENTE_CACHE_KEY)) {
            return self::SUCCESS;
        }

        $inscritos = InterlabInscrito::certificadosPendentes()
            ->orderBy('data_inscricao')->orderBy('id')->lazy(100);

        foreach ($inscritos as $inscrito) {
            if ($action->execute($inscrito->id)) {
                $this->info("Certificado da inscrição {$inscrito->id} enfileirado.");

                return self::SUCCESS;
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
    }
}

<?php

namespace App\Console\Commands;

use App\Actions\EnviarCertificadoAction;
use App\Models\CursoInscrito;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class EnviarCertificadoPendenteCommand extends Command
{
    public const SEM_CERTIFICADO_PENDENTE_CACHE_KEY = 'certificados:sem-pendencia';

    protected $signature = 'certificados:enviar-pendente';

    protected $description = 'Envia certificado para o inscrito em curso mais antigo com pagamento efetivado e sem certificado emitido';

    public function handle(EnviarCertificadoAction $action): void
    {
        if (Cache::has(self::SEM_CERTIFICADO_PENDENTE_CACHE_KEY)) {
            return;
        }

        $inscritos = CursoInscrito::certificadosPendentes()
            ->orderBy('data_inscricao')->orderBy('id')->lazy(100);

        foreach ($inscritos as $inscrito) {
            try {
                if ($action->executePendente($inscrito->id)) {
                    $this->info("Certificado do curso enfileirado para o inscrito {$inscrito->id}.");

                    return;
                }
            } catch (ValidationException) {
                continue;
            }
        }

        if (Cache::add(self::SEM_CERTIFICADO_PENDENTE_CACHE_KEY, true, now('America/Sao_Paulo')->addDay()->startOfDay())) {
            Mail::raw(
                'Não há certificados pendentes para envio no momento.',
                function ($message) {
                    $message->to('sistema@redemetrologica.com.br')->subject('Notificação: Sem certificados pendentes');
                }
            );
        }

        $this->info('Nenhum inscrito apto para envio. Consultas suspensas até a próxima meia-noite.');
    }
}

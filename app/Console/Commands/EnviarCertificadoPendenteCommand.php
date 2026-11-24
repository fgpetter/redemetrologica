<?php

namespace App\Console\Commands;

use App\Actions\EnviarCertificadoAction;
use App\Console\Commands\Concerns\EnviaCertificadosPendentes;
use App\Mail\AvisoSemCertificadosPendentesMail;
use App\Models\CursoInscrito;
use App\Support\ProcessosAgendados;
use Illuminate\Console\Command;

class EnviarCertificadoPendenteCommand extends Command
{
    use EnviaCertificadosPendentes;

    protected $signature = 'certificados:enviar-pendente';

    /**
     * Enfileira o envio de certificados para inscrições em curso pendentes com pagamento efetivado. 
     * Se não houver inscrições aptas, envia um aviso ao e-mail do sistema.
     * 
     * Caso de uso: processar certificados manualmente ou na rotina agendada.
     */

    protected $description = 'Enfileira os certificados de cursos pendentes com pagamento efetivado';

    public function handle(EnviarCertificadoAction $action): int
    {
        return ProcessosAgendados::execucao($this->getName(), fn (): int => $this->enviarCertificadosPendentes(
            CursoInscrito::certificadosPendentes(),
            fn (int $id): bool => $action->executePendente($id, agendado: ProcessosAgendados::emRodada()),
            new AvisoSemCertificadosPendentesMail(
                'Notificação: Sem certificados pendentes',
                'Não há certificados pendentes para envio no momento.',
            ),
        ));
    }
}

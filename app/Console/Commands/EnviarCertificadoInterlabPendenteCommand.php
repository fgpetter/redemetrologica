<?php

namespace App\Console\Commands;

use App\Actions\EnfileirarCertificadoInterlabPendenteAction;
use App\Console\Commands\Concerns\EnviaCertificadosPendentes;
use App\Mail\AvisoSemCertificadosPendentesMail;
use App\Models\InterlabInscrito;
use App\Support\ProcessosAgendados;
use Illuminate\Console\Command;

class EnviarCertificadoInterlabPendenteCommand extends Command
{
    use EnviaCertificadosPendentes;

    protected $signature = 'interlab:enviar-certificado-pendente';

    protected $description = 'Enfileira os certificados Interlab pendentes com pagamento efetivado';

    public function handle(EnfileirarCertificadoInterlabPendenteAction $action): int
    {
        return ProcessosAgendados::execucao($this->getName(), fn (): int => $this->enviarCertificadosPendentes(
            InterlabInscrito::certificadosPendentes(),
            fn (int $id): bool => $action->execute($id, agendado: ProcessosAgendados::emRodada()),
            new AvisoSemCertificadosPendentesMail(
                'Notificação: Sem certificados Interlab pendentes',
                'Não há certificados Interlab aptos para envio no momento.',
            ),
        ));
    }
}

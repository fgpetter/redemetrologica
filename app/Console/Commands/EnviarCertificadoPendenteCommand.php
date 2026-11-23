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

    protected $description = 'Enfileira os certificados de cursos pendentes com pagamento efetivado';

    public function handle(EnviarCertificadoAction $action): int
    {
        return ProcessosAgendados::execucao($this->getName(), fn (): int => $this->enviarCertificadosPendentes(
            CursoInscrito::certificadosPendentes(),
            fn (int $id): bool => $action->executePendente($id, agendado: isset(ProcessosAgendados::contexto()['rodada_id'])),
            new AvisoSemCertificadosPendentesMail(
                'Notificação: Sem certificados pendentes',
                'Não há certificados pendentes para envio no momento.',
            ),
        ));
    }
}

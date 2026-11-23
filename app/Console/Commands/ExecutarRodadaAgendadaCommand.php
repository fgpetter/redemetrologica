<?php

namespace App\Console\Commands;

use App\Actions\Agendamentos\ExecutarRodadaAgendada;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class ExecutarRodadaAgendadaCommand extends Command
{
    protected $signature = 'agendamentos:executar-rodada
                            {--verificar : Registra um aviso se a rodada estiver aberta}
                            {--retomar : Executa as etapas ainda não iniciadas de uma rodada interrompida}';

    protected $description = 'Executa os comandos de enfileiramento da rodada diária';

    public function handle(ExecutarRodadaAgendada $action): int
    {
        $dia = CarbonImmutable::now('America/Sao_Paulo');

        if ($this->option('verificar')) {
            if ($action->verificar($dia)) {
                $this->warn('A rodada de e-mails agendados ainda está aberta.');
            }

            return self::SUCCESS;
        }

        return $action->execute($dia, fn (string $comando): int => $this->call($comando), (bool) $this->option('retomar'));
    }
}

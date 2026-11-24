<?php

namespace App\Console\Commands;

use App\Actions\Agendamentos\ExecutarRodadaAgendada;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class ExecutarRodadaAgendadaCommand extends Command
{
    protected $signature = 'agendamentos:executar-rodada';

    protected $description = 'Executa os comandos de enfileiramento da rodada diária';

    public function handle(ExecutarRodadaAgendada $action): int
    {
        return $action->execute(CarbonImmutable::now('America/Sao_Paulo'), fn (string $comando): int => $this->call($comando));
    }
}

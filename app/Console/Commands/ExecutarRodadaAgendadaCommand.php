<?php

namespace App\Console\Commands;

use App\Actions\Agendamentos\ExecutarRodadaAgendada;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class ExecutarRodadaAgendadaCommand extends Command
{
    protected $signature = 'agendamentos:executar-rodada';

    /**
     * Executa, em sequência, os comandos da rodada diária de certificados, senhas Interlab, lembretes de avaliações e e-mails de cursos. 
     * Registra falhas e retorna erro se algum processo falhar.
     * 
     * Caso de uso: centralizar a rotina diária de enfileiramento.
     */
    protected $description = 'Executa os comandos de enfileiramento da rodada diária';

    public function handle(ExecutarRodadaAgendada $action): int
    {
        return $action->execute(CarbonImmutable::now('America/Sao_Paulo'), fn (string $comando): int => $this->call($comando));
    }
}

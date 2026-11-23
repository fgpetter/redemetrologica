<?php

namespace App\Console\Commands;

use App\Actions\Interlab\EnfileirarRastreioItensLotePostagemAction;
use App\Support\ProcessosAgendados;
use Illuminate\Console\Command;

class RastrearItensLotePostagemCommand extends Command
{
    protected $signature = 'interlab:rastrear-itens-postagem {--agendado : Encaminha os avisos para a fila de e-mails agendados}';

    protected $description = 'Enfileira jobs de rastreio SRO para itens de lote de postagem ainda não entregues';

    public function handle(EnfileirarRastreioItensLotePostagemAction $action): int
    {
        if ($this->option('agendado')) {
            return ProcessosAgendados::execucao($this->getName(), fn (): int => $this->enfileirar($action, true));
        }

        return $this->enfileirar($action, false);
    }

    private function enfileirar(EnfileirarRastreioItensLotePostagemAction $action, bool $agendado): int
    {
        $resultado = $action->execute(agendado: $agendado);

        if ($agendado) {
            ProcessosAgendados::resumo($resultado);
        }

        $this->info("Enfileirados: {$resultado['enfileirados']}; pulados: {$resultado['pulados']}.");

        return self::SUCCESS;
    }
}

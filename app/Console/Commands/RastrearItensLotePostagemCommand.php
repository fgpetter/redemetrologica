<?php

namespace App\Console\Commands;

use App\Actions\Interlab\EnfileirarRastreioItensLotePostagemAction;
use App\Support\ProcessosAgendados;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Context;

class RastrearItensLotePostagemCommand extends Command
{
    protected $signature = 'interlab:rastrear-itens-postagem {--agendado : Encaminha os avisos para a fila de e-mails agendados}';

    /**
     * O comando enfileira o rastreamento dos itens de lotes de postagem que têm código de objeto e ainda não foram entregues. 
     * Ele pula itens cujo rastreio já está na fila e espaça os jobs em cinco segundos.
     * 
     * Caso de uso: consultar periodicamente o status dos objetos enviados pelos Correios e atualizar o acompanhamento das entregas.
     * 
     * Execute com php artisan interlab:rastrear-itens-postagem. A opção --agendado encaminha os avisos para a fila de e-mails agendados.
     */
    protected $description = 'Enfileira jobs de rastreio SRO para itens de lote de postagem ainda não entregues';

    public function handle(EnfileirarRastreioItensLotePostagemAction $action): int
    {
        if ($this->option('agendado')) {
            return Context::scope(
                fn (): int => ProcessosAgendados::execucao($this->getName(), fn (): int => $this->enfileirar($action, true)),
                ['rastreio_agendado' => true],
            );
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

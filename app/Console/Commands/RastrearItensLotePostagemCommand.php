<?php

namespace App\Console\Commands;

use App\Actions\Interlab\EnfileirarRastreioItensLotePostagemAction;
use App\Support\Observabilidade\ResumoExecucao;
use Illuminate\Console\Command;

class RastrearItensLotePostagemCommand extends Command
{
    protected $signature = 'interlab:rastrear-itens-postagem';

    protected $description = 'Enfileira jobs de rastreio SRO para itens de lote de postagem ainda não entregues';

    public function handle(EnfileirarRastreioItensLotePostagemAction $action): int
    {
        $resumo = new ResumoExecucao('RastrearItensLotePostagemCommand', [], 'stack');

        return $resumo->executar(function (ResumoExecucao $resumo) use ($action): int {

            $resultado = $action->execute();
            $resumo->incrementar('enfileirados', $resultado['enfileirados']);
            $resumo->incrementar('pulados', $resultado['pulados']);
            $resumo->resultado($resultado['enfileirados'] > 0 ? 'enfileirado' : 'ignorado', $resultado['enfileirados'] > 0 ? null : 'sem_candidatos');

            $this->info("Enfileirados: {$resultado['enfileirados']}; pulados: {$resultado['pulados']}.");

            return self::SUCCESS;
        });
    }
}

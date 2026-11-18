<?php

namespace App\Console\Commands;

use App\Actions\Interlab\RetomarInterlabLotesPostagemOrfaosAction;
use App\Support\Observabilidade\ResumoExecucao;
use Illuminate\Console\Command;

class RetomarInterlabLotesPostagemOrfaosCommand extends Command
{
    protected $signature = 'interlab:retomar-lotes-postagem-orfaos';

    protected $description = 'Reenfileira ProcessarInterlabLotePostagemJob para lotes M3 órfãos (sem job na fila)';

    public function handle(RetomarInterlabLotesPostagemOrfaosAction $action): int
    {
        $resumo = new ResumoExecucao('RetomarInterlabLotesPostagemOrfaosCommand', [], 'stack');

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

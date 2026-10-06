<?php

namespace App\Console\Commands;

use App\Actions\Interlab\RetomarInterlabLotesPostagemOrfaosAction;
use Illuminate\Console\Command;

class RetomarInterlabLotesPostagemOrfaosCommand extends Command
{
    protected $signature = 'interlab:retomar-lotes-postagem-orfaos';

    protected $description = 'Reenfileira ProcessarInterlabLotePostagemJob para lotes M3 órfãos (sem job na fila)';

    public function handle(RetomarInterlabLotesPostagemOrfaosAction $action): int
    {
        $resultado = $action->execute();

        $this->info("Enfileirados: {$resultado['enfileirados']}; pulados: {$resultado['pulados']}.");

        return self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Actions\Interlab\EnfileirarRastreioItensLotePostagemAction;
use Illuminate\Console\Command;

class RastrearItensLotePostagemCommand extends Command
{
    protected $signature = 'interlab:rastrear-itens-postagem';

    protected $description = 'Enfileira jobs de rastreio SRO para itens de lote de postagem ainda não entregues';

    public function handle(EnfileirarRastreioItensLotePostagemAction $action): int
    {
        $resultado = $action->execute();

        $this->info("Enfileirados: {$resultado['enfileirados']}; pulados: {$resultado['pulados']}.");

        return self::SUCCESS;
    }
}

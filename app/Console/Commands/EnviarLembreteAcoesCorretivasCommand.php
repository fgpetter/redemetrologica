<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\EnviaLembretesPrazoAvaliacao;
use Illuminate\Console\Command;

class EnviarLembreteAcoesCorretivasCommand extends Command
{
    use EnviaLembretesPrazoAvaliacao;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'avaliacoes:enviar-lembrete-acoes-corretivas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia lembretes de prazo de ações corretivas';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        return $this->enviarLembretes('data_acoes_corretivas', 'Ações Corretivas');
    }
}

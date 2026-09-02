<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\EnviaLembretesPrazoAvaliacao;
use Illuminate\Console\Command;

class EnviarLembretePropostaAcoesCorretivasCommand extends Command
{
    use EnviaLembretesPrazoAvaliacao;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'avaliacoes:enviar-lembrete-proposta-acoes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia lembretes de prazo de proposta de ações corretivas';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        return $this->enviarLembretes('data_proposta_acoes_corretivas', 'Proposta Ações Corretivas');
    }
}

<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\EnviaLembretesPrazoAvaliacao;
use Illuminate\Console\Command;

class EnviarLembretePropostaAcoesCorretivasCommand extends Command
{
    use EnviaLembretesPrazoAvaliacao;

    protected $signature = 'avaliacoes:enviar-lembrete-proposta-acoes';

    /**
     * Enfileira e-mails para laboratórios com prazo de proposta de ações corretivas no próximo dia útil. 
     * Não envia lembretes nos fins de semana e, se o laboratório não tiver e-mail cadastrado, avisa o sistema.
     * 
     * Usa a trait EnviaLembretesPrazoAvaliacao (uma classe compartilhada) para processar o envio.
     * 
     * Caso de uso: rodar diariamente para lembrar os laboratórios de prazos próximos.
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

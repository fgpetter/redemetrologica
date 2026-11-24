<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\EnviaLembretesPrazoAvaliacao;
use Illuminate\Console\Command;

class EnviarLembreteAcoesCorretivasCommand extends Command
{
    use EnviaLembretesPrazoAvaliacao;

    protected $signature = 'avaliacoes:enviar-lembrete-acoes-corretivas';

    /**
     * EnviarLembreteAcoesCorretivasCommand enfileira e-mails para laboratórios com prazo de ações corretivas no próximo dia útil. 
     * Não envia lembretes nos fins de semana e, se o laboratório não tiver e-mail cadastrado, avisa o sistema.
     * 
     * Usa a trait EnviaLembretesPrazoAvaliacao (uma classe compartilhada) para processar o envio.
     * 
     * Caso de uso: rodar diariamente para lembrar os laboratórios de prazos próximos.
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

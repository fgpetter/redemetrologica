<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\EnviaLembretesPrazoAvaliacao;
use Illuminate\Console\Command;

class EnviarLembreteProcedimentoLaboratorioCommand extends Command
{
    use EnviaLembretesPrazoAvaliacao;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'avaliacoes:enviar-lembrete-proc-laboratorio';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia lembretes de prazo de procedimento laboratório';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        return $this->enviarLembretes('data_proc_laboratorio', 'Procedimento Laboratório');
    }
}

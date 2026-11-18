<?php

namespace App\Console\Commands;

use App\Actions\CriarEnviarSenhaAnalistaAction;
use App\Actions\Interlab\CriarEnviarSenhaLaboratorioAction;
use App\Models\InterlabInscrito;
use App\Support\Observabilidade\ResumoExecucao;
use Illuminate\Console\Command;

class ProcessarSenhasInterlabConfirmadoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:processar-senhas-interlab-confirmado';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enfileira geração e envio de senha para inscritos/analistas em agendas CONFIRMADO com senha_enviada pendente';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $resumo = new ResumoExecucao('ProcessarSenhasInterlabConfirmadoCommand', [], 'stack');

        return $resumo->executar(function (ResumoExecucao $resumo): int {

            $inscritos = InterlabInscrito::query()
                ->whereHas('agendaInterlab', fn ($query) => $query->where('status', 'CONFIRMADO'))
                ->with(['agendaInterlab.interlab', 'analistas', 'laboratorio', 'empresa', 'pessoa'])
                ->orderBy('id')
                ->get();

            if ($inscritos->isEmpty()) {
                $this->info('Nenhum inscrito elegível encontrado.');

                return self::SUCCESS;
            }

            $enfileirados = 0;
            $pulados = 0;
            $index = 0;

            foreach ($inscritos as $inscrito) {
                $interlab = $inscrito->agendaInterlab?->interlab;

                if (empty($interlab?->tag)) {
                    $resumo->incrementar('interlabs_sem_tag');
                    $resumo->resultado('impedido', 'interlab_sem_tag');
                    $this->warn("Inscrito {$inscrito->id} ignorado: interlab sem tag.");
                    $pulados++;

                    continue;
                }

                if (($interlab->avaliacao ?? null) === 'ANALISTA') {
                    foreach ($inscrito->analistas as $analista) {
                        if ($analista->senha_enviada !== null) {
                            continue;
                        }

                        $index++;
                        app(CriarEnviarSenhaAnalistaAction::class)->execute(
                            $inscrito,
                            $analista,
                            $index * 15,
                        );
                        $enfileirados++;
                    }

                    continue;
                }

                if ($inscrito->senha_enviada !== null) {
                    continue;
                }

                $index++;
                app(CriarEnviarSenhaLaboratorioAction::class)->execute($inscrito, $index * 15);
                $enfileirados++;
            }

            if ($pulados === 0) {
                $resumo->resultado($enfileirados > 0 ? 'enfileirado' : 'ignorado');
            }
            $resumo->incrementar('enfileirados', (int) $enfileirados);
            $resumo->incrementar('pulados', (int) $pulados);
            $this->info("Enfileirados {$enfileirados} envio(s). Ignorados {$pulados}.");

            return self::SUCCESS;
        });
    }
}

<?php

namespace App\Console\Commands;

use App\Actions\CriarEnviarSenhaAnalistaAction;
use App\Actions\CriarEnviarSenhaLaboratorioAction;
use App\Models\InterlabInscrito;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

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
                Log::warning('ProcessarSenhasInterlabConfirmado: interlab sem tag, envio ignorado.', [
                    'inscrito_id' => $inscrito->id,
                    'agenda_interlab_id' => $inscrito->agenda_interlab_id,
                ]);
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

        $this->info("Enfileirados {$enfileirados} envio(s). Ignorados {$pulados}.");

        return self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Jobs\GerarEEnviarSenhaInterlabJob;
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
    protected $description = 'Enfileira geração e envio de senha para inscritos em agendas CONFIRMADO com senha_enviada pendente';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $inscritos = InterlabInscrito::query()
            ->whereNull('senha_enviada')
            ->whereHas('agendaInterlab', fn ($query) => $query->where('status', 'CONFIRMADO'))
            ->with(['agendaInterlab.interlab'])
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
            if (empty($inscrito->agendaInterlab?->interlab?->tag)) {
                Log::warning('ProcessarSenhasInterlabConfirmado: interlab sem tag, envio ignorado.', [
                    'inscrito_id' => $inscrito->id,
                    'agenda_interlab_id' => $inscrito->agenda_interlab_id,
                ]);
                $this->warn("Inscrito {$inscrito->id} ignorado: interlab sem tag.");
                $pulados++;

                continue;
            }

            $index++;
            GerarEEnviarSenhaInterlabJob::dispatch($inscrito->id)
                ->delay(now()->addSeconds($index * 15));

            $enfileirados++;
        }

        $this->info("Enfileirados {$enfileirados} inscrito(s). Ignorados {$pulados}.");

        return self::SUCCESS;
    }
}

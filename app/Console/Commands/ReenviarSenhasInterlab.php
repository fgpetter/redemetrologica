<?php

namespace App\Console\Commands;

use App\Actions\CriarEnviarSenhaInterlabAction;
use App\Models\AgendaInterlab;
use Illuminate\Console\Command;

class ReenviarSenhasInterlab extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reenviar-senhas-interlab {agenda_interlab_id : ID da agenda_interlabs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reenvia senha do interlab para todos os laboratorios inscritos na agenda informada';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $agendaInterlabId = (int) $this->argument('agenda_interlab_id');
        $agendaInterlab = AgendaInterlab::query()->find($agendaInterlabId);

        if (! $agendaInterlab) {
            $this->error('Agenda interlab nao encontrada.');

            return self::FAILURE;
        }

        $inscritos = $agendaInterlab->inscritos()
            ->with(['pessoa', 'laboratorio', 'empresa', 'agendaInterlab.interlab'])
            ->orderBy('id')
            ->get();

        if ($inscritos->isEmpty()) {
            $this->warn('Nenhum inscrito encontrado para a agenda informada.');

            return self::SUCCESS;
        }

        foreach ($inscritos as $index => $inscrito) {
            app(CriarEnviarSenhaInterlabAction::class)->execute(
                inscrito: $inscrito,
                delaySecs: ($index + 1) * 30,
            );
        }

        $this->info("Processados {$inscritos->count()} inscrito(s).");

        return self::SUCCESS;
    }
}

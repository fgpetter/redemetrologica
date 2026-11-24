<?php

namespace App\Console\Commands;

use App\Actions\CriarEnviarSenhaAnalistaAction;
use App\Actions\Interlab\CriarEnviarSenhaLaboratorioAction;
use App\Models\InterlabInscrito;
use App\Support\ProcessosAgendados;
use Illuminate\Console\Command;
use Throwable;

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
    public function handle(CriarEnviarSenhaAnalistaAction $analistaAction, CriarEnviarSenhaLaboratorioAction $laboratorioAction): int
    {
        return ProcessosAgendados::execucao($this->getName(), fn (): int => $this->processarSenhas($analistaAction, $laboratorioAction));
    }

    private function processarSenhas(CriarEnviarSenhaAnalistaAction $analistaAction, CriarEnviarSenhaLaboratorioAction $laboratorioAction): int
    {
        $inscritos = InterlabInscrito::query()
            ->whereHas('agendaInterlab', fn ($query) => $query->where('status', 'CONFIRMADO'))
            ->with(['agendaInterlab.interlab', 'analistas', 'laboratorio', 'empresa', 'pessoa'])
            ->orderBy('id')
            ->lazyById(100);

        $processados = 0;
        $pulados = 0;
        $falhas = 0;
        $atraso = 0;
        $agendado = ProcessosAgendados::emRodada();
        $intervalo = $agendado ? 0 : 15;

        foreach ($inscritos as $inscrito) {
            $interlab = $inscrito->agendaInterlab?->interlab;

            if (empty($interlab?->tag)) {
                ProcessosAgendados::log('senha_interlab_sem_tag', [
                    'inscrito_id' => $inscrito->id,
                    'agenda_interlab_id' => $inscrito->agenda_interlab_id,
                ], 'warning');
                $pulados++;

                continue;
            }

            if (($interlab->avaliacao ?? null) === 'ANALISTA') {
                foreach ($inscrito->analistas as $analista) {
                    if ($analista->senha_enviada !== null) {
                        continue;
                    }

                    $atraso += $intervalo;

                    try {
                        $analistaAction->execute($inscrito, $analista, $atraso, agendado: $agendado);
                        $processados++;
                    } catch (Throwable $exception) {
                        $falhas++;
                        ProcessosAgendados::log('senha_analista_falha_enfileiramento', [
                            'inscrito_id' => $inscrito->id,
                            'analista_id' => $analista->id,
                            'exception_class' => $exception::class,
                        ], 'error');
                    }
                }

                continue;
            }

            if ($inscrito->senha_enviada !== null) {
                continue;
            }

            $atraso += $intervalo;

            try {
                $laboratorioAction->execute($inscrito, $atraso, agendado: $agendado);
                $processados++;
            } catch (Throwable $exception) {
                $falhas++;
                ProcessosAgendados::log('senha_laboratorio_falha_enfileiramento', [
                    'inscrito_id' => $inscrito->id,
                    'exception_class' => $exception::class,
                ], 'error');
            }
        }

        ProcessosAgendados::resumo([
            'processados' => $processados,
            'ignorados_sem_tag' => $pulados,
            'falhas' => $falhas,
        ]);
        $this->info("Processados {$processados} envio(s). Ignorados {$pulados}. Falhas {$falhas}.");

        return $falhas > 0 ? self::FAILURE : self::SUCCESS;
    }
}

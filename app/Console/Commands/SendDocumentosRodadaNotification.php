<?php

namespace App\Console\Commands;

use App\Jobs\EnviarDocumentoRodadaJob;
use App\Models\InterlabRodada;
use Illuminate\Console\Command;

class SendDocumentosRodadaNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-documentos-rodada-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia notificações de documentos de rodada interlab para inscritos';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $tipos = [
            'envio_amostras' => [
                'data' => 'data_envio_amostras',
                'arquivo' => 'arquivo_envio_amostras',
                'notification' => 'envio_amostras_notification',
            ],
            'inicio_ensaios' => [
                'data' => 'data_inicio_ensaios',
                'arquivo' => 'arquivo_inicio_ensaios',
                'notification' => 'inicio_ensaios_notification',
            ],
            'limite_envio_resultados' => [
                'data' => 'data_limite_envio_resultados',
                'arquivo' => 'arquivo_limite_envio_resultados',
                'notification' => 'limite_envio_resultados_notification',
            ],
            'divulgacao_relatorios' => [
                'data' => 'data_divulgacao_relatorios',
                'arquivo' => 'arquivo_divulgacao_relatorios',
                'notification' => 'divulgacao_relatorios_notification',
            ],
        ];

        $delay = 0;

        foreach ($tipos as $tipo => $campos) {
            $rodadas = InterlabRodada::query()
                ->whereDate($campos['data'], today())
                ->whereNull($campos['notification'])
                ->whereHas('agendainterlab')
                ->with('agendainterlab.inscritos.pessoa')
                ->get();

            foreach ($rodadas as $rodada) {
                foreach ($rodada->agendainterlab->inscritos as $inscrito) {
                    EnviarDocumentoRodadaJob::dispatch($inscrito, $rodada, $tipo)
                        ->delay(now()->addSeconds($delay));

                    $delay += 30;
                }

                $rodada->update([$campos['notification'] => now()]);
            }
        }
    }
}

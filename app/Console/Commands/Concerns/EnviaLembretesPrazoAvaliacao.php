<?php

namespace App\Console\Commands\Concerns;

use App\Jobs\EnviarEmailAgendadoJob;
use App\Mail\AvisoLembretePrazoSemEmailMail;
use App\Mail\LembretePrazoAvaliacaoMail;
use App\Models\AgendaAvaliacao;
use App\Support\ProcessosAgendados;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Mail;
use Throwable;

trait EnviaLembretesPrazoAvaliacao
{
    protected function enviarLembretes(string $coluna, string $rotulo): int
    {
        return ProcessosAgendados::execucao($this->getName(), fn (): int => $this->processarLembretes($coluna, $rotulo));
    }

    private function processarLembretes(string $coluna, string $rotulo): int
    {
        $contexto = ProcessosAgendados::contexto();
        $dia = CarbonImmutable::parse($contexto['data_rodada'] ?? 'today', 'America/Sao_Paulo');

        if ($dia->isWeekend()) {
            $this->info('Fim de semana: lembretes não enviados.');
            ProcessosAgendados::resumo(['enfileirados' => 0, 'fim_de_semana' => true]);

            return self::SUCCESS;
        }

        $dataPrazo = $dia->addDays($dia->isFriday() ? 3 : 1)->toDateString();

        $enfileirados = 0;
        $semEmail = 0;
        $falhas = 0;

        $avaliacoes = AgendaAvaliacao::query()
            ->whereDate($coluna, $dataPrazo)
            ->with('laboratorio')
            ->orderBy('id')
            ->lazyById(100);

        foreach ($avaliacoes as $avaliacao) {
            $email = trim((string) $avaliacao->laboratorio?->email);

            try {
                if (filled($email)) {
                    $aviso = new LembretePrazoAvaliacaoMail($avaliacao, $rotulo, $dataPrazo);
                } else {
                    $email = 'sistema@redemetrologica.com.br';
                    $aviso = new AvisoLembretePrazoSemEmailMail($avaliacao, $rotulo, $dataPrazo);
                    $semEmail++;
                }

                if (isset($contexto['rodada_id'])) {
                    EnviarEmailAgendadoJob::dispatch($aviso, $email, $contexto);
                } else {
                    Mail::to($email)->queue($aviso);
                }

                $enfileirados++;
            } catch (Throwable $exception) {
                $falhas++;
                ProcessosAgendados::log('lembrete_avaliacao_falha_enfileiramento', [
                    'agenda_avaliacao_id' => $avaliacao->id,
                    'exception_class' => $exception::class,
                ], 'error');
            }
        }

        ProcessosAgendados::resumo([
            'data_prazo' => $dataPrazo,
            'enfileirados' => $enfileirados,
            'avisos_sem_email' => $semEmail,
            'falhas' => $falhas,
        ]);
        $this->info("Enfileirados {$enfileirados} lembrete(s) para {$rotulo}.");

        return $falhas > 0 ? self::FAILURE : self::SUCCESS;
    }
}

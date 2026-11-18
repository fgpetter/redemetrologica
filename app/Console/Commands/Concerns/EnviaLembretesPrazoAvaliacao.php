<?php

namespace App\Console\Commands\Concerns;

use App\Mail\AvisoLembretePrazoSemEmailMail;
use App\Mail\LembretePrazoAvaliacaoMail;
use App\Models\AgendaAvaliacao;
use App\Support\Observabilidade\ResumoExecucao;
use Illuminate\Support\Facades\Mail;

trait EnviaLembretesPrazoAvaliacao
{
    protected function enviarLembretes(string $coluna, string $rotulo): int
    {
        $resumo = new ResumoExecucao('avaliacoes.lembretes', ['tipo' => $coluna]);

        return $resumo->executar(function ($resumo) use ($coluna, $rotulo): int {
            if (now()->isWeekend()) {
                $resumo->resultado('ignorado', 'fim_de_semana');
                $this->info('Fim de semana: lembretes não enviados.');

                return self::SUCCESS;
            }

            $dataPrazo = now()->isFriday()
                ? now()->addDays(3)->toDateString()
                : now()->addDay()->toDateString();

            $enviados = 0;

            AgendaAvaliacao::query()
                ->whereDate($coluna, $dataPrazo)
                ->with('laboratorio')
                ->orderBy('id')
                ->get()
                ->each(function (AgendaAvaliacao $avaliacao) use ($dataPrazo, $rotulo, &$enviados, $resumo): void {
                    $email = trim((string) $avaliacao->laboratorio?->email);

                    if (filled($email)) {
                        Mail::to($email)->queue(new LembretePrazoAvaliacaoMail($avaliacao, $rotulo, $dataPrazo));
                        $resumo->incrementar('destino_funcional');
                    } else {
                        Mail::to('sistema@redemetrologica.com.br')->queue(new AvisoLembretePrazoSemEmailMail($avaliacao, $rotulo, $dataPrazo));
                        $resumo->incrementar('alternativa_administrativa');
                    }

                    $enviados++;
                });

            $resumo->resultado($enviados > 0 ? 'enfileirado' : 'ignorado', $enviados > 0 ? null : 'sem_candidatos');
            $this->info("Enfileirados {$enviados} lembrete(s) para {$rotulo}.");

            return self::SUCCESS;
        });
    }
}

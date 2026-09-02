<?php

namespace App\Console\Commands\Concerns;

use App\Mail\AvisoLembretePrazoSemEmailMail;
use App\Mail\LembretePrazoAvaliacaoMail;
use App\Models\AgendaAvaliacao;
use Illuminate\Support\Facades\Mail;

trait EnviaLembretesPrazoAvaliacao
{
    protected function enviarLembretes(string $coluna, string $rotulo): int
    {
        if (now()->isWeekend()) {
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
            ->each(function (AgendaAvaliacao $avaliacao) use ($dataPrazo, $rotulo, &$enviados): void {
                $email = trim((string) $avaliacao->laboratorio?->email);

                if (filled($email)) {
                    Mail::to($email)->queue(new LembretePrazoAvaliacaoMail($avaliacao, $rotulo, $dataPrazo));
                } else {
                    Mail::to('sistema@redemetrologica.com.br')->queue(new AvisoLembretePrazoSemEmailMail($avaliacao, $rotulo, $dataPrazo));
                }

                $enviados++;
            });

        $this->info("Enfileirados {$enviados} lembrete(s) para {$rotulo}.");

        return self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\InterlabInscrito;
use App\Support\Observabilidade\ResumoExecucao;
use Illuminate\Console\Command;

class BackfillCertificadosInterlabEmitidosCommand extends Command
{
    protected $signature = 'interlab:backfill-certificados-emitidos {--dry-run : Apenas conta as inscrições elegíveis}';

    protected $description = 'Marca certificados antigos de agendas concluídas e inscrições pagas, sem enviar e-mails';

    public function handle(): int
    {
        $resumo = new ResumoExecucao('BackfillCertificadosInterlabEmitidosCommand', [], 'stack');
        $resumo->contexto(['simulacao' => (bool) $this->option('dry-run')]);

        return $resumo->executar(function (ResumoExecucao $resumo): int {

            $instante = now();
            $query = InterlabInscrito::certificadosPendentes();

            if ($this->option('dry-run')) {
                $elegiveis = $query->count();
                $resumo->incrementar('elegiveis', $elegiveis);
                $this->info("Inscrições elegíveis: {$elegiveis}. Nenhum registro alterado.");

                return self::SUCCESS;
            }

            $alterados = $query->toBase()->update([
                'certificado_emitido' => $instante,
                'updated_at' => $instante,
            ]);
            $resumo->incrementar('alterados', $alterados);
            $this->info("Certificados marcados: {$alterados}.");

            return self::SUCCESS;
        });
    }
}

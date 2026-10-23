<?php

namespace App\Console\Commands;

use App\Models\InterlabInscrito;
use Illuminate\Console\Command;

class BackfillCertificadosInterlabEmitidosCommand extends Command
{
    protected $signature = 'interlab:backfill-certificados-emitidos {--dry-run : Apenas conta as inscrições elegíveis}';

    protected $description = 'Marca certificados antigos de agendas concluídas e inscrições pagas, sem enviar e-mails';

    public function handle(): int
    {
        $instante = now();
        $query = InterlabInscrito::certificadosPendentes();

        if ($this->option('dry-run')) {
            $this->info("Inscrições elegíveis: {$query->count()}. Nenhum registro alterado.");

            return self::SUCCESS;
        }

        $alterados = $query->toBase()->update([
            'certificado_emitido' => $instante,
            'updated_at' => $instante,
        ]);
        $this->info("Certificados marcados: {$alterados}.");

        return self::SUCCESS;
    }
}

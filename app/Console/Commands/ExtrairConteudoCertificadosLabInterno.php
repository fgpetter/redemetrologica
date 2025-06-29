<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LaboratorioInterno;
use Smalot\PdfParser\Parser;
use Illuminate\Support\Facades\File;

class ExtrairConteudoCertificadosLabInterno extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:extrair-conteudo-certificados-lab-interno';
    protected $description = 'Extrai o conteúdo dos PDFs dos certificados e preenche a coluna conteudo_certificado';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando extração dos certificados...');

        $parser = new Parser();

        // Usar cursor() para iterar sobre os registros de forma eficiente em termos de memória.
        $query = LaboratorioInterno::whereNotNull('certificado')->whereNull('conteudo_certificado');

        foreach ($query->cursor() as $lab) {

            $path = public_path('laboratorios-certificados/' . $lab->certificado);

            if (!File::exists($path)) {
                $this->warn(" Certificado não encontrado para o laboratório ID {$lab->id} no caminho: {$path}");
                continue;
            }

            try {
                $pdf = $parser->parseFile($path);
                $texto = $pdf->getText();
                $lab->conteudo_certificado = $texto;
                $lab->save();

                $this->info(" ✔ Conteúdo extraído para o laboratório ID {$lab->id}");
            } catch (\Exception $e) {
                $this->error(" Erro ao processar o certificado do laboratório ID {$lab->id}: " . $e->getMessage());
                continue;
            }
        }

        $this->info("\nExtração concluída com sucesso!");
        return self::SUCCESS;
    }
}

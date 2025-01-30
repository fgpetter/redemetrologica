<?php

namespace App\Console\Commands;

use App\Mail\CertificadosDeletedNotification;
use App\Models\LaboratorioInterno;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;

class DeleteUnusedCertificados extends Command
{
    protected $signature = 'certificados:clean';
    protected $description = 'Delete certificados from laboratorios internos where site is false';

    public function handle()
    {
        $deletedFiles = [];
        
        $laboratorios = LaboratorioInterno::where('site', false)
            ->whereNotNull('certificado')
            ->get();

        foreach ($laboratorios as $laboratorio) {
            $certificadoPath = public_path('certificados-lab/' . $laboratorio->certificado);
            
            if (File::exists($certificadoPath)) {
                File::delete($certificadoPath);
                $laboratorio->update(['certificado' => null]);
                $deletedFiles[] = $laboratorio->certificado;
                $this->info("Certificado deletado: {$laboratorio->certificado}");
            }
        }

        // Envia email com a lista de arquivos removidos
        Mail::to('ti@redemetrologica.com.br')
            ->send(new CertificadosDeletedNotification($deletedFiles));

        $this->info('Limpeza de certificados concluída');
    }
}
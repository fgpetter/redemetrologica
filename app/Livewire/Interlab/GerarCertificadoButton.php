<?php

namespace App\Livewire\Interlab;

use Livewire\Component;
use Illuminate\Support\Str;
use App\Models\InterlabInscrito;
use Spatie\LaravelPdf\Facades\Pdf;
use Illuminate\Support\Facades\Mail;
use App\Mail\CertificadoInterlabMail;
use Illuminate\Support\Facades\Storage;
use App\Jobs\GerarCertificadoInterlabJob;

class GerarCertificadoButton extends Component
{
    public $participanteId;

    public function mount($participanteId)
    {
        $this->participanteId = $participanteId;
    }

    public function gerarCertificado()
    {
        // Dispatch do job para fila
        GerarCertificadoInterlabJob::dispatch($this->participanteId);
        
        // Dispatch evento JavaScript para mostrar o alerta
        $this->dispatch('show-success-alert', message: 'Certificado está sendo gerado e será enviado por email em breve.');
    }

    public function render()
    {
        return view('livewire.interlab.gerar-certificado-button');
    }
}
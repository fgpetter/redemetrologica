<?php

namespace App\Livewire\Interlab;

use App\Models\InterlabInscrito;
use Illuminate\Support\Facades\Mail;
use App\Mail\CertificadoInterlabMail;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Spatie\LaravelPdf\Facades\Pdf;

class GerarCertificadoButton extends Component
{
    public $participanteId;

    public function mount($participanteId)
    {
        $this->participanteId = $participanteId;
    }

    public function gerarCertificado()
    {
        $participante = InterlabInscrito::findOrFail($this->participanteId);
       

        $tempPath = 'temp/' . uniqid() . '.pdf';

        
        Pdf::view('certificados.certificado-interlab', [
            'participante' => $participante,
            
        ])->save(Storage::path($tempPath));

        Mail::to($participante->pessoa->email)->send(new CertificadoInterlabMail($participante, Storage::path($tempPath)));

        Storage::delete($tempPath);

        return redirect(request()->header('Referer'))->with('success', 'Certificado enviado com sucesso.');
    }

    public function render()
    {
        return view('livewire.interlab.gerar-certificado-button');
    }
}
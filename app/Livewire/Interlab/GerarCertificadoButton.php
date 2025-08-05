<?php

namespace App\Livewire\Interlab;

use Livewire\Component;
use Illuminate\Support\Str;
use App\Models\InterlabInscrito;
use Spatie\LaravelPdf\Facades\Pdf;
use Illuminate\Support\Facades\Mail;
use App\Mail\CertificadoInterlabMail;
use Illuminate\Support\Facades\Storage;

class GerarCertificadoButton extends Component
{
    public $participanteId;

    public function mount($participanteId)
    {
        $this->participanteId = $participanteId;
    }

    public function gerarCertificado()
    {
        $participante = InterlabInscrito::with(['laboratorio','agendaInterlab.interlab'])->findOrFail($this->participanteId);

        $labNameSlug = Str::slug($participante->laboratorio->nome);
        $fileName = 'certificado_interlab_' . $labNameSlug . '_' . $participante->agendaInterlab->interlab->id . '.pdf';
        $Path = 'public/docs/certificados/' . $fileName;

        //update em interlabinscrito com certificado_emitido = data atual e certificado_path = $fileName;
        $participante->certificado_emitido = now();
        $participante->certificado_path = $fileName;
        $participante->save();

        // Remove o arquivo existente, se houver
        if (Storage::exists($Path)) {
            Storage::delete($Path);
        }

        Pdf::view('certificados.certificado-interlab', [
            'participante' => $participante,
        ])
        ->save(Storage::path($Path));
        
        Mail::to($participante->laboratorio->email)->send(new CertificadoInterlabMail($participante, Storage::path($Path)));
        
        return redirect(request()->header('Referer'))->with('success', 'Certificado enviado com sucesso.');
        
        //download do arquivo pdf no navegador
        // return response()->download(Storage::path($Path), 'certificado-interlab.pdf')->deleteFileAfterSend(true);
    }

    public function render()
    {
        return view('livewire.interlab.gerar-certificado-button');
    }
}
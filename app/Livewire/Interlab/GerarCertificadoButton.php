<?php

namespace App\Livewire\Interlab;

use Livewire\Component;
use App\Models\InterlabInscrito;

class GerarCertificadoButton extends Component
{
    public $participanteId;

    public function mount($participanteId)
    {
        $this->participanteId = $participanteId;
    }

    public function gerarCertificado()
    {
        try {
            $inscrito = InterlabInscrito::findOrFail($this->participanteId);
            
            app(\App\Actions\EnviarCertificadoInterlabAction::class)->execute($inscrito);
            
            // Dispatch evento JavaScript para mostrar o alerta
            $this->dispatch('show-success-alert', message: 'Certificado está sendo gerado e será enviado por email em breve.');
        } catch (\Exception $e) {
            $this->dispatch('show-error-alert', message: 'Erro ao gerar certificado: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.interlab.gerar-certificado-button');
    }
}
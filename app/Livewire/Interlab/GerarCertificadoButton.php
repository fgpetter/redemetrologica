<?php

namespace App\Livewire\Interlab;

use Livewire\Component;
use App\Models\InterlabInscrito;
use App\Actions\EnviarCertificadoInterlabAction;

class GerarCertificadoButton extends Component
{
    public $participanteId;
    public $email = '';
    public $showModal = false;

    public function mount($participanteId)
    {
        $this->participanteId = $participanteId;
        
        // Carregar email do participante
        $inscrito = InterlabInscrito::find($participanteId);
        if ($inscrito) {
            $this->email = $inscrito->email ?? '';
        }
    }

    public function confirmarEnvio()
    {
        $inscrito = InterlabInscrito::find($this->participanteId);
        if ($inscrito) {
            $this->email = $inscrito->email ?? '';
        }
        
        $this->showModal = true;
    }

    public function enviarCertificado()
    {
        $this->validate([
            'email' => 'required|email|max:191',
        ], [
            'email.required' => 'O email é obrigatório.',
            'email.email' => 'O email deve ser um endereço válido.',
            'email.max' => 'O email deve ter no máximo 191 caracteres.',
        ]);

        try {
            $inscrito = InterlabInscrito::findOrFail($this->participanteId);
            
            app(EnviarCertificadoInterlabAction::class)->execute($inscrito, $this->email);
            
            $this->showModal = false;
            $this->dispatch('show-success-alert', message: 'Certificado está sendo gerado e será enviado por email em breve.');
        } catch (\Exception $e) {
            $this->dispatch('show-error-alert', message: 'Erro ao gerar certificado: ' . $e->getMessage());
        }
    }

    public function fecharModal()
    {
        $this->showModal = false;
        $this->reset('email');
    }

    public function render()
    {
        return view('livewire.interlab.gerar-certificado-button');
    }
}
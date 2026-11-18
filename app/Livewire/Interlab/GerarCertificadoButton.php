<?php

namespace App\Livewire\Interlab;

use App\Actions\EnviarCertificadoInterlabAction;
use App\Livewire\Forms\CertificadoInterlabForm;
use App\Models\InterlabInscrito;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class GerarCertificadoButton extends Component
{
    public CertificadoInterlabForm $form;

    public $participanteId;

    public $email = '';

    public $showModal = false;

    public function mount($participanteId, $email = null)
    {
        $this->participanteId = $participanteId;
        $this->email = $email ?? '';
        if ($this->email === '' && $participanteId) {
            $inscrito = InterlabInscrito::find($participanteId);
            if ($inscrito) {
                $this->email = $inscrito->email ?? '';
            }
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

        try {
            $this->form->email = (string) $this->email;
            $this->form->validate();
            $inscrito = InterlabInscrito::findOrFail($this->participanteId);

            app(EnviarCertificadoInterlabAction::class)->execute($inscrito, $this->email);

            $this->showModal = false;
            $this->dispatch('show-success-alert', message: 'Certificado está sendo gerado e será enviado por email em breve.');
        } catch (ValidationException $e) {
            Log::channel('validation')->warning('certificado.validacao', ['campos' => array_keys($e->errors())]);
            $this->dispatch('show-error-alert', message: 'Verifique o e-mail informado para o certificado.');
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('show-error-alert', message: 'Não foi possível solicitar o certificado. Tente novamente.');
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

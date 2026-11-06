<?php

namespace App\Livewire\PainelCliente;

use App\Livewire\Forms\BuscaCnpjForm;
use App\Models\InterlabInscrito;
use App\Models\Pessoa;
use Livewire\Component;

class BuscaCNPJ extends Component
{
    public BuscaCnpjForm $form;

    public $isVisible = true;

    public string $tutorialSection = 'como-comecar';

    public function mount(): void
    {
        $interlabId = session('interlab')?->id;

        if (! $interlabId) {
            return;
        }

        $hasExistingInscriptions = InterlabInscrito::where('pessoa_id', request()->user()->pessoa->id)
            ->where('agenda_interlab_id', $interlabId)
            ->exists();

        if ($hasExistingInscriptions) {
            $this->tutorialSection = 'outro-cnpj';
        }
    }

    public function render()
    {
        return view('livewire.painel-cliente.busca-cnpj');
    }

    public function ProcuraCnpj()
    {
        $this->form->validate();

        $cnpjLimpo = preg_replace('/[^0-9]/', '', $this->form->BuscaCnpj);

        $empresa = Pessoa::where('cpf_cnpj', $cnpjLimpo)
            ->where('tipo_pessoa', 'PJ')
            ->first();

        $searchTerm = $this->form->BuscaCnpj;
        $this->form->BuscaCnpj = null;

        $this->isVisible = false;

        if ($empresa) {
            $this->dispatch('cnpjFound', id_pessoa: $empresa->id);
        } else {
            $this->dispatch('cnpjNotFound', cnpj: $searchTerm);
        }
    }
}

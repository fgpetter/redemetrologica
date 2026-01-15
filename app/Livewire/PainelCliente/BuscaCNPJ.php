<?php

namespace App\Livewire\PainelCliente;

use App\Models\Pessoa;
use App\Models\InterlabInscrito;
use Livewire\Component;

class BuscaCNPJ extends Component
{
    public $BuscaCnpj;
    public $isVisible = true; 

    protected $rules = [
        'BuscaCnpj' => ['required', 'cnpj'],
    ];

    protected $messages = [
        'BuscaCnpj.cnpj' => 'Não é um CNPJ válido.',
        'BuscaCnpj.required' => 'Digite um CNPJ para cadastro.',
    ];

    public function render()
    {
        return view('livewire.painel-cliente.busca-cnpj');
    }

    public function ProcuraCnpj()
    {
        $this->validate();

        $cnpjLimpo = preg_replace('/[^0-9]/', '', $this->BuscaCnpj);

        $empresa = Pessoa::where('cpf_cnpj', $cnpjLimpo)
            ->where('tipo_pessoa', 'PJ')
            ->first();
        
        $searchTerm = $this->BuscaCnpj;
        $this->BuscaCnpj = null; 

        $this->isVisible = false;

        if ($empresa) {
            $this->dispatch('cnpjFound', id_pessoa: $empresa->id);
        } else {
            $this->dispatch('cnpjNotFound', cnpj: $searchTerm);
        }
    }
}

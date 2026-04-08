<?php

namespace App\Livewire\Enderecos;

use App\Models\Pessoa;
use Livewire\Component;
use Livewire\Attributes\On;

class Listview extends Component
{
    public Pessoa $pessoa;
    public ?string $enderecoAtivo = null;
    public string $modalKey = 'init';

    public function abrirModal($uid = null)
    {
        $this->enderecoAtivo = $uid;
        $this->modalKey = ($uid ?? 'new') . '-' . uniqid();
    }

    #[On('refresh-enderecos-list')]
    public function render()
    {
        $enderecos = collect();
        if ($this->pessoa->endereco) {
            $enderecos->push($this->pessoa->endereco);
        }
        if ($this->pessoa->enderecoCobranca) {
            $enderecos->push($this->pessoa->enderecoCobranca);
        }

        return view('livewire.enderecos.listview', [
            'enderecos' => $enderecos
        ]);
    }
}
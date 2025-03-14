<?php

namespace App\Livewire\Enderecos;

use Livewire\Component;
use App\Models\Pessoa;
use App\Models\Endereco;

class Listview extends Component
{
    public Pessoa $pessoa;
    public ?string $enderecoAtivo = null;

    public function abrirModal($uid = null)
    {
        $this->enderecoAtivo = $uid;
    }


    public function render()
    {
        return view('livewire.enderecos.listview', [
            'enderecos' => $this->pessoa->enderecos
        ]);
    }
}
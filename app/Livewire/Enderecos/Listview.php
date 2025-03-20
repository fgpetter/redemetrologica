<?php

namespace App\Livewire\Enderecos;

use App\Models\Pessoa;
use Livewire\Component;
use Livewire\Attributes\On;

class Listview extends Component
{
    public Pessoa $pessoa;
    public ?string $enderecoAtivo = null;

    public function abrirModal($uid = null)
    {
        $this->enderecoAtivo = $uid;
    }

    #[On('refresh-enderecos-list')]
    public function render()
    {
        return view('livewire.enderecos.listview', [
            'enderecos' => $this->pessoa->enderecos
        ]);
    }
}
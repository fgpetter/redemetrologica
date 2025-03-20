<?php

namespace App\Livewire\DadosBancarios;

use App\Models\Pessoa;
use Livewire\Component;
use Livewire\Attributes\On;

class Listview extends Component
{
    public Pessoa $pessoa;
    public ?string $contaAtiva = null;

    public function abrirModal($uid = null)
    {
        $this->contaAtiva = $uid;
    }

    #[On('refresh-list')]
    public function render()
    {
        return view('livewire.dados-bancarios.listview', [
            'contas' => $this->pessoa->dadosBancarios
        ]);
    }
}
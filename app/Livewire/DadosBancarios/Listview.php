<?php

namespace App\Livewire\DadosBancarios;

use App\Models\Pessoa;
use Livewire\Attributes\On;
use Livewire\Component;

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
        return view('livewire.dados-bancarios.list-view', [
            'contas' => $this->pessoa->dadosBancarios,
        ]);
    }
}

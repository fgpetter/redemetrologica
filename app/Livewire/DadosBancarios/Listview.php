<?php

namespace App\Livewire\DadosBancarios;

use Livewire\Component;
use App\Models\Pessoa;
use App\Models\DadoBancario;

class Listview extends Component
{
    public Pessoa $pessoa;
    public ?string $contaAtiva = null;

    public function abrirModal($uid = null)
    {
        $this->contaAtiva = $uid;
    }


    public function render()
    {
        return view('livewire.dados-bancarios.listview', [
            'contas' => $this->pessoa->dadosBancarios
        ]);
    }
}
<?php

namespace App\Livewire\PainelCliente;

use Livewire\Component;

class EncerraInscricao extends Component
{
    public function encerrarInscricoes()
    {
        session()->forget(['interlab', 'empresa', 'convite']); 
        return redirect('painel');
    }

    public function render()
    {
        return view('livewire.painel-cliente.encerra-inscricao');
    }
}

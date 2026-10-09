<?php

namespace App\Livewire\PainelCliente;

use Livewire\Component;

class InscritosInterlab extends Component
{
    public $interlabs;

    public function mount(): void
    {
        $this->interlabs = auth()->user()->pessoa
            ->interlabs()
            ->whereHas('agendaInterlab', fn ($q) => $q->where('status', '!=', 'CONCLUIDO'))
            ->with('agendaInterlab.interlab')
            ->get();
    }

    public function render()
    {
        return view('livewire.painel-cliente.inscritos-interlab');
    }
}

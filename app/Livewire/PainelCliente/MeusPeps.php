<?php

namespace App\Livewire\PainelCliente;

use Illuminate\Support\Collection;
use Livewire\Component;

class MeusPeps extends Component
{
    public Collection $interlabs;

    public function mount(): void
    {
        $this->interlabs = auth()->user()->pessoa
            ?->interlabs()
            ->with([
                'agendaInterlab.interlab',
                'agendaInterlab.materiais',
                'laboratorio.analistas',
                'laboratorio.endereco',
                'empresa',
            ])
            ->latest('id')
            ->get() ?? collect();
    }

    public function render()
    {
        return view('livewire.painel-cliente.meus-peps');
    }
}

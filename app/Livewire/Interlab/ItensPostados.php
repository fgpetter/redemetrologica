<?php

namespace App\Livewire\Interlab;

use App\Models\AgendaInterlab;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ItensPostados extends Component
{
    public AgendaInterlab $agenda;

    public function mount(AgendaInterlab $agenda): void
    {
        $this->agenda = $agenda;
    }

    public function render(): View
    {
        return view('livewire.interlab.itens-postados', [
            'lotesConcluidos' => $this->agenda->lotesPostagemConcluidos(),
        ]);
    }
}

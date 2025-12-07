<?php

namespace App\Livewire\Rodadas;

use App\Models\AgendaInterlab;
use App\Models\InterlabRodada;
use Livewire\Component;
use Livewire\Attributes\On;

class Listview extends Component
{
    public AgendaInterlab $agendainterlab;
    public ?string $rodadaAtiva = null;

    public function abrirModal($uid = null): void
    {
        $this->rodadaAtiva = $uid;
    }

    public function deletar($uid): void
    {
        $rodada = InterlabRodada::where('uid', $uid)->first();
        if ($rodada) {
            $rodada->delete();
            $this->dispatch('refresh-rodadas-list');
        }
    }

    #[On('refresh-rodadas-list')]
    public function render()
    {
        return view('livewire.rodadas.listview', [
            'rodadas' => $this->agendainterlab->rodadas,
        ]);
    }
}


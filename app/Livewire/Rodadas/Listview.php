<?php

namespace App\Livewire\Rodadas;

use App\Models\AgendaInterlab;
use Livewire\Attributes\On;
use Livewire\Component;

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
        abort_unless(auth()->user()?->hasPermissionTo(['funcionario', 'admin']), 404);
        $rodada = $this->agendainterlab->rodadas()->where('uid', $uid)->firstOrFail();
        if ($rodada) {
            $rodada->delete();
            $this->dispatch('refresh-rodadas-'.$this->agendainterlab->id);
        }
    }

    #[On('refresh-rodadas-{agendainterlab.id}')]
    public function render()
    {
        return view('livewire.rodadas.list-view', [
            'rodadas' => $this->agendainterlab->rodadas,
        ]);
    }
}

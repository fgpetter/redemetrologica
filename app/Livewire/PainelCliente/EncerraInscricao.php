<?php

namespace App\Livewire\PainelCliente;

use Livewire\Attributes\On;
use Livewire\Component;
use App\Models\InterlabInscrito;

class EncerraInscricao extends Component
{
    public $empresaId = null;
    public $inscritosCount = 0;

    #[On('empresaSaved')]
    public function setEmpresa($empresa_id)
    {
        $this->empresaId = $empresa_id;
        $this->loadInscritosCount();
    }

    #[On('novoLabInscritoSaved')]
    public function reloadCount()
    {
        $this->loadInscritosCount();
    }

    public function loadInscritosCount()
    {
        if (!$this->empresaId) {
            $this->inscritosCount = 0;
            return;
        }

        $interlab = session('interlab');

        $this->inscritosCount = InterlabInscrito::where('pessoa_id', auth()->user()->pessoa->id)
            ->where('empresa_id', $this->empresaId)
            ->where('agenda_interlab_id', $interlab->id)
            ->count();
    }

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

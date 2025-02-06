<?php

namespace App\View\Components\Painel\PainelCliente;

use Closure;
use Illuminate\View\Component;
use App\Models\InterlabInscrito;
use Illuminate\Contracts\View\View;

class LaboratoriosInscritosInterlab extends Component
{
    public $inscricoes_interlab;

    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $this->inscricoes_interlab = InterlabInscrito::with(['agendaInterlab', 'pessoa', 'empresa', 'laboratorio.endereco'])
        ->where('pessoa_id', auth()->user()->pessoa->id)
        ->get();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.painel.painel-cliente.laboratorios-inscritos-interlab');
    }
}

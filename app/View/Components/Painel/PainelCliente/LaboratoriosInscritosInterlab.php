<?php

namespace App\View\Components\Painel\PainelCliente;

use Closure;
use App\Models\Pessoa;
use Illuminate\View\Component;
use App\Models\InterlabInscrito;
use Illuminate\Contracts\View\View;

class LaboratoriosInscritosInterlab extends Component
{
    public $inscricao_interlab;
    public $lab_inscritos;
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $this->inscricao_interlab = InterlabInscrito::with(['agendaInterlab'])
        ->where('empresa_id', auth()->user()->pessoa->empresas->first()->id)
        ->first();

        $this->lab_inscritos = InterlabInscrito::with(['agendaInterlab', 'pessoa', 'empresa', 'laboratorio.endereco'])
        ->where('empresa_id', auth()->user()->pessoa->empresas->first()->id)
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

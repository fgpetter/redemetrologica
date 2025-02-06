<?php

namespace App\View\Components\Painel\PainelCliente;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Models\Pessoa;
use App\Models\AgendaInterlab;
use App\Models\InterlabInscrito;

class ConfirmaInscricaoInterlab extends Component
{
    public Pessoa $pessoa;
    public AgendaInterlab $interlab;
    public $inscritos;

    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        /** @var Pessoa */
        $this->pessoa = Pessoa::where('user_id', auth()->user()->id)
            ->with('empresas')
            ->with('interlabs')
            ->firstOrFail();

        /** @var AgendaInterlab */
        $this->interlab = session('interlab') ?? null;

        /** @var \App\Models\InterlabInscrito */
        $this->inscritos = InterlabInscrito::with('laboratorio')
            ->where('pessoa_id', $this->pessoa->id)
            ->where('agenda_interlab_id', $this->interlab->id)
            ->get() ?? null;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.painel.painel-cliente.confirma-inscricao-interlab');
    }
}

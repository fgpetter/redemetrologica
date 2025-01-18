<?php

namespace App\View\Components\Painel\PainelCliente;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Models\Pessoa;
use App\Models\AgendaInterlab;

class ConfirmaInscricaoInterlab extends Component
{
    public Pessoa $pessoa;
    public Pessoa|null $empresa;
    public AgendaInterlab $interlab;
    public bool $convidado;
    public bool $inscrito;

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
    
        /** @var Pessoa */
        $this->empresa = session('empresa') ?? $this->pessoa->empresas()->first() ?? null;

        /** @var AgendaInterlab */
        $this->interlab = session('interlab') ?? null;

        /** @var bool */
        $this->convidado = session('convidado') ?? false;

        /** @var bool */
        $this->inscrito = $this->pessoa->interlabs->where('agenda_interlab_id', $this->interlab->id)->isNotEmpty();

    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.painel.painel-cliente.confirma-inscricao-interlab');
    }
}

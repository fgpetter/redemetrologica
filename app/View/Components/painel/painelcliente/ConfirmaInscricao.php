<?php

namespace App\View\Components\painel\painelCliente;

use Closure;
use App\Models\Pessoa;
use App\Models\AgendaCursos;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;

class ConfirmaInscricao extends Component
{
    public Pessoa|null $empresa;
    public AgendaCursos $curso;
    public Pessoa $pessoa;
    public bool $convite = false;

    /**
    * Create a new component instance.
    */
    public function __construct()
    {
        $this->empresa = session('empresa') ?? null;
        $this->curso = session('curso');
        $this->pessoa = Pessoa::where('user_id', auth()->user()->id)->first();
    }

    /**
    * Get the view / contents that represent the component.
    */
    public function render(): View|Closure|string
    {
        // TODO verificar se empresa atrelada a pessoa é a mesma empresa do convite

        if(!session('empresa')){
            $this->empresa = $this->pessoa->empresas()->first() ?? null;
        } else {
            $this->pessoa->empresas()->sync($this->empresa->id);
            $this->convite = true;
        }

        return view('components.painel.painel-cliente.confirma-inscricao');
    }
}

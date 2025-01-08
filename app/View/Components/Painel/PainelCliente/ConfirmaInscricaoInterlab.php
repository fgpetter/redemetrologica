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
    public Pessoa|null $empresa;
    public AgendaInterlab $interlab;
    public Pessoa $pessoa;
    public bool $convite;

    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $this->empresa = session('empresa') ?? null;
        $this->interlab = session('interlab') ?? null;
        $this->pessoa = Pessoa::where('user_id', auth()->user()->id)->first();
        $this->convite = session('convite') ?? false;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        // verifica se usuário tem uma pessoa associada
        if(!$this->pessoa) {
            logger()->error('Usuário não tem uma pessoa associada', ['user_id' => auth()->user()->id]);
            abort(404);
        }

        // verifica se já está inscrita no interlab
        if( InterlabInscrito::where('agenda_interlab_id', $this->interlab->id)->where('pessoa_id', $this->pessoa->id)->exists() ) {
            session()->forget(['interlab', 'empresa']);
            return redirect('painel');
        }

        // vincula novo cadastro a uma empresa se houver id de indicação
        if(!session('empresa')){
            $this->empresa = $this->pessoa->empresas()->first() ?? null;
        } else {
            $this->pessoa->empresas()->sync($this->empresa->id);
        }

        if($this->interlab->exists()) {
            return view('components.painel.painel-cliente.confirma-inscricao-interlab');
        }
        abort(404);

    }
}

<?php

namespace App\View\Components\Painel\PainelCliente;

use App\Models\Pessoa;
use App\Models\AgendaCursos;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ConfirmaInscricao extends Component
{
    public Pessoa $pessoa;
    public Pessoa|null $empresa;
    public AgendaCursos $curso;
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
            ->with('enderecos')
            ->with('cursos')
            ->firstOrFail();

        /** @var Pessoa */
        $this->empresa = session('empresa') ?? $this->pessoa->empresas()->first() ?? null;

        /** @var AgendaCursos */
        $this->curso = session('curso') ?? null;

        /** @var bool */
        $this->convidado = session('convidado') ?? false;

        /** @var bool */
        $this->inscrito = $this->pessoa->cursos->where('agenda_curso_id', $this->curso->id)->isNotEmpty();
    }

    /**
    * Get the view / contents that represent the component.
    */
    public function render(): View | RedirectResponse
    {
        return view('components.painel.painel-cliente.confirma-inscricao');
    }
}

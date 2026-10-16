<?php

namespace App\Livewire\Suporte;

use App\Actions\Suporte\OrdenarDemandasSuporteAction;
use App\Enums\SuporteStatus;
use App\Models\SuporteDemanda;
use App\Traits\Livewire\Suporte\RegistraErrosSuporte;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class SuporteTable extends Component
{
    use RegistraErrosSuporte;
    use WithPagination;

    #[Url(as: 'q')]
    public string $busca = '';

    #[Url]
    public string $status = 'ativas';

    public function updated(string $property): void
    {
        $this->resetPage();
    }

    /** @return array{busca: string, status: string} */
    protected function filtros(): array
    {
        $this->validate([
            'busca' => ['string', 'max:255'],
            'status' => [Rule::in(array_merge(['ativas', 'todos'], array_column(SuporteStatus::cases(), 'value')))],
        ]);

        return $this->only(['busca', 'status']);
    }

    protected function getQuery(): Builder
    {
        return SuporteDemanda::comFiltros($this->filtros())->orderBy('prioridade')->orderBy('id');
    }

    public function mover(string $uid, string $direcao, OrdenarDemandasSuporteAction $action): void
    {
        $action->execute(auth()->user(), $uid, $direcao, $this->filtros());
    }

    public function render(): View
    {
        SuporteDemanda::autorizarAcesso(auth()->user());
        $query = $this->getQuery();
        $limites = (clone $query)->reorder()->selectRaw('MIN(prioridade) as primeira, MAX(prioridade) as ultima')->first();

        return view('livewire.suporte.suporte-table', [
            'demandas' => $query->with('autor')->withCount('atividades')->paginate(15),
            'statuses' => SuporteStatus::cases(),
            'primeiraPrioridade' => $limites?->primeira,
            'ultimaPrioridade' => $limites?->ultima,
        ]);
    }
}

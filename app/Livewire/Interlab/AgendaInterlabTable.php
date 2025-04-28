<?php

namespace App\Livewire\Interlab;

use DB;
use App\Models\Pessoa;
use Livewire\Component;
use App\Models\Interlab;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use App\Models\AgendaInterlab;
use App\Models\InterlabInscrito;

class AgendaInterlabTable extends Component
{
    use WithPagination;

    #[Url(as: 'p', history: false)]
    public $perPage = 15;

    #[Url(as: 's', history: false)]
    public $search = '';

    #[Url(as: 'st', history: false)]
    public $status = '';

    #[Url(as: 'ta', history: false)]
    public $tipo_agendamento = '';

    #[Url(as: 'sb', history: false)]
    public $sortBy = 'data_inicio';

    #[Url(as: 'sd', history: false)]
    public $sortDirection = 'ASC';

    #[Url(as: 'isv', history: false)]
    public $inscricaoSemValor = false;

    #[Url(as: 'empresa', history: false)]
    public $empresaSelecionada = '';

    public function setSortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'ASC' ? 'DESC' : 'ASC';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'ASC';
        }
    }

    public function resetFilters()
    {
        $this->reset(['search', 'status', 'inscricaoSemValor', 'empresaSelecionada']);
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['search', 'status', 'perPage', 'inscricaoSemValor', 'empresaSelecionada'])) {
            $this->resetPage();
        }
    }

    protected function getQuery()
    {
        $query = AgendaInterlab::with(['interlab', 'inscritos'])
            ->withCount('inscritos');

        $query = $this->applySearchFilter($query);
        $query = $this->applyStatusFilter($query);
        $query = $this->applyInscricaoSemValorFilter($query);
        $query = $this->applyEmpresaFilter($query);
        $query = $this->applySorting($query);

        return $query;
    }

    protected function applySearchFilter($query)
    {
        $search = trim($this->search);

        return $query->when($search, function ($query) use ($search) {
            $query->where(function ($q) use ($search) {
                $q->orWhereHas('interlab', function ($subQuery) use ($search) {
                    $subQuery->where('nome', 'like', "%{$search}%");
                });

                $q->orWhereHas('inscritos.empresa', function ($subQuery) use ($search) {
                    $subQuery->where('nome_razao', 'like', "%{$search}%");
                });
            });
        });
    }

    protected function applyStatusFilter($query)
    {
        return $query->when($this->status, fn($query) =>
            $query->where('status', $this->status)
        );
    }

    protected function applyInscricaoSemValorFilter($query)
    {
        return $query->when($this->inscricaoSemValor, function ($query) {
            $query->whereHas('inscritos', function ($subQuery) {
                $subQuery->where(function ($q) {
                    $q->whereNull('valor')->orWhere('valor', 0);
                });
            });
        });
    }

    protected function applyEmpresaFilter($query)
    {
        return $query->when($this->empresaSelecionada, function ($query) {
            $query->whereHas('inscritos.empresa', function ($subQuery) {
                $subQuery->where('id', $this->empresaSelecionada);
            });
        });
    }

    protected function applySorting($query)
    {
        return $query->when($this->sortBy, function ($query) {
            if ($this->sortBy === 'nome') {
                $query->orderBy(
                    Interlab::select('nome')
                        ->whereColumn('interlabs.id', 'agenda_interlabs.interlab_id'),
                    $this->sortDirection
                );
            } elseif ($this->sortBy === 'inscritos') {
                $query->orderBy('inscritos_count', $this->sortDirection);
            } else {
                $query->orderBy($this->sortBy, $this->sortDirection);
            }
        });
    }

    public function getEmpresasProperty()
    {
        return Pessoa::query()
            ->select(['id', 'cpf_cnpj', 'nome_razao'])
            ->whereIn('id', InterlabInscrito::query()
                ->select('empresa_id')
                ->whereNotNull('empresa_id')
                ->distinct()
            )
            ->orderBy('nome_razao')
            ->get();
    }

    public function render()
    {
        $agendainterlabs = $this->getQuery()->paginate($this->perPage);

        return view('livewire.interlab.agenda-interlab-table', [
            'agendainterlabs' => $agendainterlabs,
        ]);
    }
}

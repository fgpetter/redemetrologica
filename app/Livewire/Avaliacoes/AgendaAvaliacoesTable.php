<?php

namespace App\Livewire\Avaliacoes;

use App\Models\AgendaAvaliacao;
use App\Models\AreaAvaliada;
use App\Models\Avaliador;
use App\Models\Laboratorio;
use App\Models\TipoAvaliacao;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class AgendaAvaliacoesTable extends Component
{
    use WithPagination;

    // Paginação
    #[Url(as: 'p', history: false)]
    public $perPage = 15;

    // Ordenação
    #[Url(as: 'sb', history: false)]
    public $sortBy = 'data_inicio';

    #[Url(as: 'sd', history: false)]
    public $sortDirection = 'ASC';

    // Filtro entre Datas
    #[Url(as: 'di', history: false)]
    public $dataIni = '';

    #[Url(as: 'df', history: false)]
    public $dataFim = '';

    // Propriedades dos filtros.
    #[Url(as: 'com', history: false)]
    public $comite = '';

    #[Url(as: 'sp', history: false)]
    public $status_proposta = '';

    #[Url(as: 'tav', history: false)]
    public $tipo_avaliacao_id = '';

    #[Url(as: 'av', history: false)]
    public $avaliador_id = '';

    public function setSortBy($field)
    {
        if (! in_array($field, ['data_inicio', 'laboratorio_id', 'fr_28', 'status_proposta', 'carta_marcacao', 'data_proposta_acoes_corretivas', 'data_acoes_corretivas', 'acoes_aceitas', 'comite'], true)) {
            return;
        }

        $this->resetPage();

        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'ASC' ? 'DESC' : 'ASC';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'ASC';
        }
    }

    public function resetFilters()
    {
        $this->resetPage();

        $this->reset(['dataIni', 'dataFim', 'comite', 'status_proposta', 'tipo_avaliacao_id', 'avaliador_id']);
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['perPage', 'dataIni', 'dataFim', 'comite', 'status_proposta', 'tipo_avaliacao_id', 'avaliador_id'])) {
            $this->resetPage();
        }
    }

    // Ordenação
    protected function applySorting($query)
    {
        return $query->when($this->sortBy, function ($query) {
            if ($this->sortBy === 'laboratorio_id') {
                $query->orderBy(
                    Laboratorio::select('nome_laboratorio')
                        ->whereColumn('laboratorios.id', 'agenda_avaliacoes.laboratorio_id'),
                    $this->sortDirection
                );
            } else {
                $query->orderBy($this->sortBy, $this->sortDirection);
            }
        });
    }

    // Filtro por datas
    protected function applyDatasFilters($query)
    {
        $query->when($this->dataIni, fn ($query, $date) => $query->where('data_inicio', '>=', $date));
        $query->when($this->dataFim, fn ($query, $date) => $query->where('data_inicio', '<=', $date));

        return $query;
    }

    // Filtro por comite
    protected function applyComiteFilter($query)
    {
        return $query->when($this->comite, fn ($query, $comite) => $query->where('comite', $comite));
    }

    // Filtro por status_proposta
    protected function applyStatusFilter($query)
    {
        return $query->when($this->status_proposta, fn ($query, $status) => $query->where('status_proposta', $status));
    }

    // Filtro por tipo_avaliacao
    protected function applyTipoAvaliacaoFilter($query)
    {
        return $query->when($this->tipo_avaliacao_id, fn ($query, $id) => $query->where('tipo_avaliacao_id', $id));
    }

    // Filtro por avaliador
    protected function applyAvaliadorFilter($query)
    {
        return $query->when($this->avaliador_id, function ($query, $id) {
            $query->whereHas('areas.avaliador', function ($subQuery) use ($id) {
                $subQuery->where('id', $id);
            });
        });
    }

    // carrega propriedade dos tipos de avaliação
    public function getTiposProperty()
    {
        return TipoAvaliacao::all();
    }

    // carrega propriedade dos avaliadores
    public function getAvaliadoresProperty()
    {
        return Avaliador::whereIn('id', AreaAvaliada::select('avaliador_id')->distinct())->with('pessoa')->get();
    }

    // carrega propriedade dos laboratórios
    public function getLaboratoriosProperty()
    {
        return Laboratorio::with('pessoa')->whereHas('pessoa')->get();
    }

    // Monta Query
    protected function getQuery()
    {
        $query = AgendaAvaliacao::with('laboratorio');

        $query = $this->applySorting($query);
        $query = $this->applyDatasFilters($query);
        $query = $this->applyComiteFilter($query);
        $query = $this->applyStatusFilter($query);
        $query = $this->applyTipoAvaliacaoFilter($query);
        $query = $this->applyAvaliadorFilter($query);

        return $query;
    }

    public function render()
    {
        $this->sortBy = in_array($this->sortBy, ['data_inicio', 'laboratorio_id', 'fr_28', 'status_proposta', 'carta_marcacao', 'data_proposta_acoes_corretivas', 'data_acoes_corretivas', 'acoes_aceitas', 'comite'], true) ? $this->sortBy : 'data_inicio';
        $this->sortDirection = in_array($this->sortDirection, ['ASC', 'DESC'], true) ? $this->sortDirection : 'ASC';
        $this->perPage = max(1, min(100, (int) $this->perPage));

        $avaliacoes = $this->getQuery()->paginate($this->perPage);

        return view('livewire.avaliacoes.agenda-avaliacoes-table', [
            'avaliacoes' => $avaliacoes,
        ]);
    }
}

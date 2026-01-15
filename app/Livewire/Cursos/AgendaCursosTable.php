<?php

namespace App\Livewire\Cursos;

use App\Models\Curso;
use Livewire\Component;
use App\Models\AgendaCursos;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use App\Models\CursoInscrito;

class AgendaCursosTable extends Component
{
    use WithPagination;

    public $tipoAgendaIni;
    //Itens exibidos por paginação
    #[Url(as : 'p', history:false)]
    public $perPage = 15;
    //Busca por status, descricao, data_inicio
    #[Url(as : 's', history:false)]
    public $search = '';
    //Filtro por Status
    #[Url(as : 'st', history:false)]
    public $status = '';
    //Filtro por tipo_agendamento
    #[Url(as : 'ta', history:false)]
    public $tipo_agendamento = '';
    //Filtro entre Datas
    #[Url(as : 'di', history:false)]
    public $dataIni = '';
    #[Url(as : 'df', history:false)]
    public $dataFim = '';
    //Ordenação
    #[Url(as : 'sb', history:false)]
    public $sortBy = 'data_inicio';
    #[Url(as : 'sd', history:false)]
    public $sortDirection = 'ASC';

    //Metodo sortBy
    public function setSortBy($sortByField){
        if($this->sortBy === $sortByField){
            $this->sortDirection = ($this->sortDirection == "ASC") ? 'DESC' : "ASC";
            return;
        }
        $this->sortBy = $sortByField;
        $this->sortDirection = 'ASC';
    }

    //Método para resetar os filtros
    public function resetFilters()
    {
        $this->reset(['search', 'status', 'dataIni', 'dataFim', 'tipo_agendamento']);
    }

    //Metodos para resetar paginação se alterar filtros
    public function updatedSearch(){
        $this->resetPage();
    }
    public function updatedStatus(){
        $this->resetPage();
    }
    public function updatedTipo_agendamento(){
        $this->resetPage();
    }
    public function updatedDataIni(){
        $this->resetPage();
    }
    public function updatedDataFim(){
        $this->resetPage();
    }
    public function updatedperPage(){
        $this->resetPage();
    }

    //Metodo mount para capturar tipoagendaini
    public function mount($tipoagendaini)
    {
        $this->tipoAgendaIni = $tipoagendaini;
    }

    //Método para construir a query (reutilizável)
    protected function getQuery()
    {
        $sortField = $this->sortBy;
        $sortDirection = $this->sortDirection;

        $query = AgendaCursos::with('curso')
            ->where(function ($query) {
                $query->whereHas('curso', function ($q) {
                    $q->where('descricao', 'like', "%{$this->search}%");
                });
            })
            ->when($this->status !== '', function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->tipoAgendaIni !== '', function ($query) {
                if ($this->tipoAgendaIni === 'ABERTA') {
                    $query->where('tipo_agendamento', '!=', 'IN-COMPANY');
                } elseif ($this->tipoAgendaIni === 'IN-COMPANY') {
                    $query->where('tipo_agendamento', '=', 'IN-COMPANY');
                }
            })
            ->when($this->tipo_agendamento !== '', function ($query) {  
                    $query->where('tipo_agendamento', $this->tipo_agendamento);
            })
            ->when($this->dataIni || $this->dataFim, function ($query) {
                if ($this->dataIni && $this->dataFim) {
                    $query->whereBetween('data_inicio', [$this->dataIni, $this->dataFim]);
                } elseif ($this->dataIni) {
                    $query->where('data_inicio', '>=', $this->dataIni);
                } else {
                    $query->where('data_inicio', '<=', $this->dataFim);
                }
            })
            ->when($sortField, function ($query) use ($sortDirection, $sortField) {
                if ($sortField === 'curso') {
                    $query->orderBy(
                        Curso::select('descricao')
                            ->whereColumn('cursos.id', 'agenda_cursos.curso_id'),
                        $sortDirection
                    );
                } else {
                    $query->orderBy($sortField, $sortDirection);
                }
            });

        return $query;
    }

    //Método render
    public function render()
    {

        $agendacursos = $this->getQuery()->paginate($this->perPage);

        return view('livewire.cursos.agenda-cursos-table', [
            'agendacursos' => $agendacursos,
            'tipoagenda' => $this->tipoAgendaIni,
        ]);
    }
}
    

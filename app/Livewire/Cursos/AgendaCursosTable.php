<?php

namespace App\Livewire\Cursos;

use App\Models\Curso;
use Livewire\Component;
use App\Models\AgendaCursos;
use App\Models\CursoInscrito;

class AgendaCursosTable extends Component
{
    //Itens exibidos por paginação
    public $perPage = 15;
    //Busca por status, descricao, data_inicio
    public $search = '';
    //Filtro por Status
    public $status = '';
    //Filtro por tipo_agendamento
    public $tipo_agendamento = '';
    //Filtro entre Datas
    public $dataIni = '';
    public $dataFim = '';
    //Ordenação
    public $sortBy = 'data_inicio';
    public $sortDirection = 'ASC';
    //Linhas selecionadas
    public $selectedRows = [];

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
        $this->reset(['search', 'selectedRows', 'status', 'tipo_agendamento', 'dataIni', 'dataFim']);
    }

     //Método para construir a query (reutilizável)
    protected function getQuery()
{
    $sortField = $this->sortBy;
    $sortDirection = $this->sortDirection;

    $query = AgendaCursos::with('curso')
        ->where(function ($query) {
            $query->where('status', 'like', "%{$this->search}%")
                ->orWhereHas('curso', function ($q) {
                    $q->where('descricao', 'like', "%{$this->search}%");
                })
                ->orWhereRaw("DATE_FORMAT(data_inicio, '%d/%m/%Y') LIKE ?", ["%{$this->search}%"]);
        })
        ->when($this->status !== '', function ($query) {
            $query->where('status', $this->status);
        })
        ->when($this->tipo_agendamento !== '', function ($query) {
            if ($this->tipo_agendamento === 'ABERTA') {
                $query->where('tipo_agendamento', '!=', 'IN-COMPANY');
            } else {
                $query->where('tipo_agendamento', $this->tipo_agendamento);
            }
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

         // Totalizador de agendas $selectedRows
        $totalValor = CursoInscrito::whereIn('agenda_curso_id', $this->selectedRows)
            ->sum('valor');

        return view('livewire.cursos.agenda-cursos-table', [
            'agendacursos' => $agendacursos,
            'tipoagenda' => $this->tipo_agendamento,
            'totalValor'   => $totalValor,
        ]);
    }
}
    

<?php

namespace App\Livewire\Cursos;

use App\Models\AgendaCursos;
use Livewire\Component;

class ListParticipantes extends Component
{
    public AgendaCursos $agendacurso;
    public string $sortBy = 'empresa';
    public string $sortDirection = 'ASC';

    /**
     * Define o campo de ordenação dos participantes e empresas
     *
     * @param string $field
     * @return void
     */
    public function setSortBy(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'ASC' ? 'DESC' : 'ASC';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'ASC';
        }
    }

    /**
     * Renderiza a tela de lista de participantes
     * 
     */
    public function render()
    {
        // Consulta os inscritos com as pessoas e empresas
        $inscritosQuery = $this->agendacurso->inscritos()->with(['pessoa', 'empresa:nome_razao']);

        if ($this->sortBy === 'nome') {
            $inscritosQuery->join('pessoas', 'curso_inscritos.pessoa_id', '=', 'pessoas.id')
                ->orderBy('pessoas.nome_razao', $this->sortDirection)
                ->select('curso_inscritos.*');
        } elseif ($this->sortBy === 'empresa') {
            $inscritosQuery->leftJoin('pessoas as empresa_pessoas', 'curso_inscritos.empresa_id', '=', 'empresa_pessoas.id')
                ->orderBy('empresa_pessoas.nome_razao', $this->sortDirection)
                ->select('curso_inscritos.*');
        } else {
            $inscritosQuery->orderBy($this->sortBy, $this->sortDirection);
        }

        return view('livewire.cursos.list-participantes', [
            'inscritos' => $inscritosQuery->get(),
        ]);
    }
}

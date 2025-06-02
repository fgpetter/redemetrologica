<?php

namespace App\Livewire\Interlab;

use Livewire\Component;
use App\Models\AgendaInterlab;
use App\Models\InterlabInscrito;
use App\Models\Pessoa;

class ListParticipantes extends Component
{


    public $idinterlab;


    public $pessoas;
    public $agendainterlab;
    public $intelabinscritos;
    public $interlabempresasinscritas;


    public $editandoValor = null;
    public $novoValor;

    /**
     * mount recebe  id e faz todo o carregamento.
     */
    public function mount(int $idinterlab)
    {
        $this->idinterlab = $idinterlab;

        $this->agendainterlab = AgendaInterlab::with([
            'despesas',
            'parametros',
            'rodadas',
        ])->findOrFail($this->idinterlab);

        $this->pessoas = Pessoa::select([
            'id',
            'uid',
            'cpf_cnpj',
            'nome_razao',
            'tipo_pessoa',
        ])
            ->orderBy('nome_razao')
            ->get();

        $this->intelabinscritos = InterlabInscrito::where('agenda_interlab_id', $this->idinterlab)
            ->with(['empresa', 'pessoa', 'laboratorio'])
            ->get();

        $empresaIds = $this->intelabinscritos
            ->pluck('empresa_id')
            ->unique()
            ->values();

        $this->interlabempresasinscritas = Pessoa::whereIn('id', $empresaIds)
            ->orderBy('nome_razao')
            ->get();
    }

    public function editarValorParticipante($participanteId)
    {
        $this->editandoValor = $participanteId;
        $participante = InterlabInscrito::findOrFail($participanteId);
        $this->novoValor = $participante->valor;
    }


    public function atualizarValorParticipante($participanteId)
    {
        $this->validate([
            'novoValor' => ['required', 'numeric', 'min:0'],
        ], [
            'novoValor.required' => 'O valor é obrigatório.',
            'novoValor.numeric'  => 'O valor deve ser numérico.',
            'novoValor.min'      => 'O valor deve ser maior ou igual a zero.',
        ]);

        $participante = InterlabInscrito::findOrFail($participanteId);
        $participante->update(['valor' => $this->novoValor]);

        $itemNaColecao = $this->intelabinscritos->firstWhere('id', $participanteId);
        if ($itemNaColecao) {
            $itemNaColecao->valor = $this->novoValor;
        }

        $this->editandoValor = null;
        $this->novoValor     = null;
    }

    public function cancelarEdicao()
    {
        $this->editandoValor = null;
        $this->novoValor     = null;
    }

    public function render()
    {
        return view('livewire.interlab.list-participantes');
    }
}

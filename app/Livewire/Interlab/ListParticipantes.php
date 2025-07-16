<?php

namespace App\Livewire\Interlab;

use App\Models\Pessoa;
use Livewire\Component;
use App\Models\AgendaInterlab;
use App\Models\InterlabInscrito;
use Illuminate\Support\Facades\Validator;

class ListParticipantes extends Component
{
    public $idinterlab;
    public $pessoas;
    public $agendainterlab;
    public $intelabinscritos;
    public $interlabempresasinscritas;
    


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


    public function atualizarValor($id, $valor)
    {
        Validator::make(
            ['id' => $id, 'valor' => $valor],
            [
                'id'    => ['required', 'exists:interlab_inscritos,id'],
                'valor' => ['required', 'numeric', 'min:0', 'max:999999.99'], // Limite máximo para evitar erro SQL
            ],
            [
                'id.required'    => 'O ID é obrigatório.',
                'id.exists'      => 'Participante não encontrado.',
                'valor.required' => 'O valor é obrigatório.',
                'valor.numeric'  => 'O valor deve ser numérico.',
                'valor.min'      => 'O valor deve ser maior ou igual a zero.',
                'valor.max'      => 'O valor máximo permitido é 999.999,99.',
            ]
        )->validate();

        $participante = InterlabInscrito::findOrFail($id);
        $participante->valor = $valor;
        $participante->save();

        if ($item = $this->intelabinscritos->firstWhere('id', $id)) {
            $item->valor = $valor;
        }
    }

    public function render()
    {
        return view('livewire.interlab.list-participantes');
    }
}

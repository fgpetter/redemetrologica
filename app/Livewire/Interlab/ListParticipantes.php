<?php

namespace App\Livewire\Interlab;

use App\Models\AgendaInterlab;
use App\Models\DadosGeraDoc;
use App\Models\InterlabInscrito;
use App\Models\Pessoa;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

class ListParticipantes extends Component
{
    public int $idinterlab;

    /** @var \Illuminate\Support\Collection|null */
    public $pessoas = null;

    public function mount(int $idinterlab, $pessoas = null)
    {
        $this->idinterlab = $idinterlab;
        $this->pessoas = $pessoas ? (is_array($pessoas) ? collect($pessoas) : $pessoas) : null;
    }

    public function atualizarValor($id, $valor)
    {
        Validator::make(
            ['id' => $id, 'valor' => $valor],
            [
                'id' => ['required', 'exists:interlab_inscritos,id'],
                'valor' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            ],
            [
                'id.required' => 'O ID é obrigatório.',
                'id.exists' => 'Participante não encontrado.',
                'valor.required' => 'O valor é obrigatório.',
                'valor.numeric' => 'O valor deve ser numérico.',
                'valor.min' => 'O valor deve ser maior ou igual a zero.',
                'valor.max' => 'O valor máximo permitido é 999.999,99.',
            ]
        )->validate();

        $participante = InterlabInscrito::findOrFail($id);
        $participante->valor = $valor;
        $participante->save();
    }

    public function render()
    {
        $agendainterlab = AgendaInterlab::findOrFail($this->idinterlab);

        $intelabinscritos = InterlabInscrito::where('agenda_interlab_id', $this->idinterlab)
            ->with(['empresa', 'pessoa', 'laboratorio.endereco', 'laboratorio.analistas', 'lancamentoFinanceiro'])
            ->orderBy('data_inscricao', 'desc')
            ->get();

        // Agrupar inscritos por empresa mantendo a ordenação por data
        $inscritosPorEmpresa = $intelabinscritos->groupBy('empresa_id');

        // Ordenar empresas pela data de inscrição mais recente de seus laboratórios
        $empresaIds = $inscritosPorEmpresa->map(function ($inscritos) {
            return [
                'empresa_id' => $inscritos->first()->empresa_id,
                'data_mais_recente' => $inscritos->first()->data_inscricao,
            ];
        })->sortByDesc('data_mais_recente')->pluck('empresa_id');

        $pessoasCollection = $this->pessoas && $this->pessoas->isNotEmpty()
            ? $this->pessoas
            : Pessoa::select(['id', 'uid', 'cpf_cnpj', 'nome_razao', 'tipo_pessoa'])->orderBy('nome_razao')->get();

        $interlabempresasinscritas = $pessoasCollection->whereIn('id', $empresaIds)
            ->sortBy(function ($empresa) use ($empresaIds) {
                return $empresaIds->search($empresa->id);
            })
            ->values();

        $pessoas = $pessoasCollection;

        // pré-carregar todos os tag_senha_doc de uma vez (evita N+1)
        $participanteIds = $intelabinscritos->pluck('id')->toArray();
        $tagsSenhaDoc = DadosGeraDoc::where('tipo', 'tag_senha')
            ->get()
            ->filter(function ($doc) use ($participanteIds) {
                $participanteId = $doc->content['participante_id'] ?? null;

                return in_array($participanteId, $participanteIds);
            })
            ->keyBy(function ($doc) {
                return $doc->content['participante_id'] ?? null;
            });

        return view('livewire.interlab.list-participantes', [
            'agendainterlab' => $agendainterlab,
            'intelabinscritos' => $intelabinscritos,
            'interlabempresasinscritas' => $interlabempresasinscritas,
            'inscritosPorEmpresa' => $inscritosPorEmpresa,
            'pessoas' => $pessoas,
            'tagsSenhaDoc' => $tagsSenhaDoc,
        ]);
    }
}

<?php

namespace App\Livewire\Rodadas;

use App\Models\AgendaInterlab;
use App\Models\InterlabRodada;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Modalform extends Component
{
    public AgendaInterlab $agendainterlab;
    public ?string $rodadaUid;
    public array $rodada = [];

    protected $rules = [
        'rodada.agenda_interlab_id' => 'required|integer',
        'rodada.descricao' => 'required|string',
        'rodada.vias' => 'required|numeric|min:1',
        'rodada.parametros' => 'nullable|array',
        'rodada.parametros.*' => 'nullable|exists:parametros,id',
    ];

    protected $messages = [
        'rodada.agenda_interlab_id.required' => 'Houve um erro ao salvar. Agenda inexistente',
        'rodada.agenda_interlab_id.integer' => 'Houve um erro ao salvar. Agenda inexistente',
        'rodada.descricao.required' => 'O campo descrição é obrigatório',
        'rodada.descricao.string' => 'O campo descrição permite somente texto',
        'rodada.vias.required' => 'O campo vias deve ser preenchido',
        'rodada.vias.numeric' => 'O campo vias deve ser um número',
        'rodada.vias.min' => 'O campo vias deve ser maior que 0',
        'rodada.parametros.array' => 'Houve um erro ao salvar. Parâmetros inválidos',
        'rodada.parametros.*.exists' => 'O parâmetro :input não existe',
    ];

    public function mount(): void
    {
        if ($this->rodadaUid) {
            $rodada = InterlabRodada::where('uid', $this->rodadaUid)->first();
            $this->rodada = $rodada->toArray();
            $this->rodada['parametros'] = $rodada->parametros->pluck('parametro_id')->toArray();
        } else {
            $this->rodada = [
                'uid' => uniqid(),
                'agenda_interlab_id' => $this->agendainterlab->id,
                'descricao' => '',
                'vias' => 1,
                'parametros' => [],
                'data_envio_amostras' => null,
                'data_inicio_ensaios' => null,
                'data_limite_envio_resultados' => null,
                'data_divulgacao_relatorios' => null,
            ];
        }
    }

    public function salvar(): void
    {
        $dadosValidados = $this->validate();

        $isNew = empty($this->rodadaUid);
        
        DB::transaction(function () use ($dadosValidados) {
            $parametros = $dadosValidados['rodada']['parametros'] ?? [];
            unset($dadosValidados['rodada']['parametros']);

            $interlab_rodada = InterlabRodada::updateOrCreate(
                ['uid' => $this->rodada['uid']],
                $dadosValidados['rodada']
            );

            $interlab_rodada->updateParametros($parametros);
        });

        $mensagem = $isNew ? 'Rodada criada com sucesso!' : 'Rodada atualizada com sucesso!';
        $this->dispatch('refresh-rodadas-list');
        $this->dispatch('show-rodada-success', message: $mensagem);
    }

    public function render()
    {
        return view('livewire.rodadas.modalform', [
            'interlabParametros' => $this->agendainterlab->parametros,
        ]);
    }
}


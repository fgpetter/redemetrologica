<?php

namespace App\Livewire\Interlab;

use App\Models\InterlabInscrito;
use App\Models\Pessoa;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use Livewire\Attributes\On;

class SubstituirResponsavel extends Component
{
    public $showModal = false;
    public $interlabInscritoId;
    public ?InterlabInscrito $interlabInscrito = null;
    public $pessoas;
    public $novo_responsavel_id;

    #[On('showSubstituirResponsavelModal')]
    public function showSubstituirResponsavelModal($interlabInscritoId)
    {
        $this->interlabInscritoId = $interlabInscritoId;
        $this->interlabInscrito = InterlabInscrito::with(['empresa', 'pessoa', 'laboratorio'])->find($this->interlabInscritoId);

        if ($this->interlabInscrito) {
            $this->pessoas = Pessoa::where('id', '!=', $this->interlabInscrito->pessoa_id)
                ->select(['id', 'nome_razao', 'tipo_pessoa', 'cpf_cnpj'])
                ->orderBy('nome_razao')
                ->get();
            $this->showModal = true;
 
        }
    }

    public function substituir()
    {
        $validated = Validator::make(
            ['novo_responsavel_id' => $this->novo_responsavel_id],
            [
                'novo_responsavel_id' => ['required', 'exists:pessoas,id'],
            ],
            [
                'novo_responsavel_id.required' => 'O responsável é obrigatório.',
                'novo_responsavel_id.exists' => 'Responsável não encontrado.',
            ]
        )->validate();

        if ($this->interlabInscrito) {
            $this->interlabInscrito->pessoa_id = $validated['novo_responsavel_id'];
            $this->interlabInscrito->save();
            
            $this->closeModal();
            return redirect(request()->header('Referer'))->with('success', 'Responsável atualizado com sucesso.');
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['interlabInscritoId', 'interlabInscrito', 'pessoas', 'novo_responsavel_id']);
    }

    public function render()
    {
        return view('livewire.interlab.substituir-responsavel');
    }
}

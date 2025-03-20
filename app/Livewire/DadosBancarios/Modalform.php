<?php

namespace App\Livewire\DadosBancarios;

use App\Models\Pessoa;
use Livewire\Component;
use App\Models\DadoBancario;

class ModalForm extends Component
{
    public Pessoa $pessoa;
    public ?string $contaUid;
    public array $conta = [];
    public bool $saved = false;

    protected $rules = [
        'conta.nome_banco' => 'required|string|min:1|max:50',
        'conta.cod_banco' => 'nullable|string|max:20',
        'conta.agencia' => 'required|string|max:20',
        'conta.conta' => 'required|string|max:50',
    ];
    protected $messages = [
        'conta.nome_banco.required' => 'O campo nome do banco é obrigatório',
        'conta.nome_banco.max' => 'Máximo de 50 caracteres permitido',
        'conta.nome_banco.min' => 'O nome do banco deve ter no mínimo 1 caracteres',
        'conta.agencia.required' => 'O campo agência é obrigatório',
        'conta.agencia.max' => 'Máximo de 20 caracteres permitido',
        'conta.conta.required' => 'O campo conta é obrigatório',
        'conta.conta.max' => 'Máximo de 50 caracteres permitido',
    ];

    public function mount()
    {
        if ($this->contaUid) {
            $conta = DadoBancario::where('uid', $this->contaUid)->first();
            $this->conta = $conta->toArray();
        } else {
            $this->conta = [
                'pessoa_id' => $this->pessoa->id,
                'uid' => uniqid()
            ];
        }
    }

    public function salvar()
    {
        $this->validate();
        DadoBancario::updateOrCreate(
            ['uid' => $this->conta['uid']],
            $this->conta
        );
        $this->dispatch('refresh-list');
        $this->saved = true;
    }

    public function render()
    {
        return view('livewire.dados-bancarios.modalform');
    }
}
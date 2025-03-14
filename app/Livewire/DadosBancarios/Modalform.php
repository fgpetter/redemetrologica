<?php

namespace App\Livewire\DadosBancarios;

use Livewire\Component;
use App\Models\DadoBancario;
use App\Models\Pessoa;

class ModalForm extends Component
{
    public Pessoa $pessoa;
    public ?string $contaUid;
    public array $conta = [];
    // adicionei essas validações para testar o comportamento do componente
    protected $rules = [
        'conta.nome_banco' => 'required|string|min:1|max:50',
        'conta.cod_banco' => 'nullable|string|max:20',
        'conta.agencia' => 'nullable|string|max:20',
        'conta.conta' => 'nullable|string|max:50',
    ];
    protected $messages = [
        'conta.nome_banco.required' => 'O campo nome do banco é obrigatório',
        'conta.nome_banco.max' => 'Máximo de 50 caracteres permitido',
        'conta.nome_banco.min' => 'O nome do banco deve ter no mínimo 1 caracteres',
    ];
       public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }


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
            // falta colocar o registro de log 
        return redirect()->to(url()->previous())->with('success', 'Conta cadastrada com sucesso');
    }

    public function render()
    {
        return view('livewire.dados-bancarios.modalform');
    }
}
<?php

namespace App\Livewire\DadosBancarios;

use App\Livewire\Forms\ContaBancariaForm;
use App\Models\Pessoa;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ModalForm extends Component
{
    public Pessoa $pessoa;

    #[Locked]
    public ?string $contaUid;

    public ContaBancariaForm $conta;

    public bool $saved = false;

    public function mount()
    {
        if ($this->contaUid) {
            $conta = $this->pessoa->dadosBancarios()->where('uid', $this->contaUid)->firstOrFail();
            $this->conta->fill($conta->only(['nome_banco', 'cod_banco', 'agencia', 'conta']));
        }
    }

    public function salvar()
    {
        abort_unless(auth()->user()?->hasPermissionTo(['funcionario', 'admin']), 404);

        $dados = $this->conta->validate();
        if ($this->contaUid) {
            $this->pessoa->dadosBancarios()->where('uid', $this->contaUid)->firstOrFail()->update($dados);
        } else {
            $this->contaUid = $this->pessoa->dadosBancarios()->create($dados)->uid;
        }
        $this->dispatch('refresh-contas-'.$this->pessoa->id);
        $this->dispatch('refresh-list');
        $this->saved = true;
    }

    public function render()
    {
        return view('livewire.dados-bancarios.modal-form');
    }
}

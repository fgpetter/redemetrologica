<?php

namespace App\Livewire\Suporte;

use App\Actions\Suporte\SalvarAtividadeSuporteAction;
use App\Models\SuporteArquivo;
use App\Models\SuporteDemanda;
use App\Traits\Livewire\Suporte\ComUploadsSuporte;
use App\Traits\Livewire\Suporte\RegistraErrosSuporte;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class AtividadeForm extends Component
{
    use ComUploadsSuporte;
    use RegistraErrosSuporte;

    #[Locked]
    public int $demandaId;

    #[Locked]
    public ?string $atividadeUid = null;

    #[Locked]
    public ?string $parentUid = null;

    #[Validate('required|string', as: 'descrição')]
    public string $descricao = '';

    public function mount(SuporteDemanda $demanda): void
    {
        SuporteDemanda::autorizarAcesso(auth()->user());
        $this->demandaId = $demanda->id;
        $this->novo();
    }

    protected function demanda(): SuporteDemanda
    {
        SuporteDemanda::autorizarAcesso(auth()->user());

        return SuporteDemanda::query()->findOrFail($this->demandaId);
    }

    public function novo(): void
    {
        $demanda = $this->demanda();
        $this->atividadeUid = null;
        $this->parentUid = null;
        $this->reset('descricao');
        $this->anexos = [];
        $this->uploads = [];
        $this->rascunho = SuporteArquivo::novoRascunho($demanda, novaAtividade: true);
        $this->resetValidation();
    }

    #[On('suporte-responder')]
    public function responder(string $uid): void
    {
        $parent = $this->demanda()->atividades()->where('uid', $uid)->whereNull('parent_id')->firstOrFail();
        $this->novo();
        $this->parentUid = $parent->uid;
    }

    #[On('suporte-editar-atividade')]
    public function editar(string $uid): void
    {
        $demanda = $this->demanda();
        $demanda->exigirAberta();
        $atividade = $demanda->atividades()->where('uid', $uid)->firstOrFail();
        abort_unless($atividade->podeEditar(auth()->user()), 403);
        $this->atividadeUid = $atividade->uid;
        $this->parentUid = null;
        $this->descricao = $atividade->descricao;
        $this->anexos = $atividade->arquivos()->where('tipo', 'anexo')->pluck('uid')->all();
        $this->rascunho = SuporteArquivo::novoRascunho($demanda, $atividade);
        $this->resetValidation();
    }

    #[On('suporte-atualizado')]
    public function atualizar(): void
    {
        $this->novo();
    }

    public function salvar(SalvarAtividadeSuporteAction $action): void
    {
        $this->validate();
        $action->execute(auth()->user(), $this->demanda(), $this->descricao, $this->rascunho, $this->anexos, $this->parentUid, $this->atividadeUid);
        $this->novo();
        $this->dispatch('suporte-atualizado');
        $this->dispatch('suporte-sucesso', message: 'Atividade salva com sucesso.');
    }

    public function render(): View
    {
        $demanda = $this->demanda();
        if ($this->atividadeUid && $demanda->status->encerrada()) {
            $this->novo();
        }

        return view('livewire.suporte.atividade-form', [
            'parent' => $this->parentUid ? $demanda->atividades()->with('owner')->where('uid', $this->parentUid)->firstOrFail() : null,
            'arquivosSelecionados' => $this->anexosSelecionados(),
        ]);
    }
}

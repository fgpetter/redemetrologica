<?php

namespace App\Livewire\Suporte;

use App\Actions\Suporte\SalvarDemandaSuporteAction;
use App\Enums\SuporteClassificacao;
use App\Enums\SuporteStatus;
use App\Livewire\Forms\SuporteDemandaForm;
use App\Models\SuporteArquivo;
use App\Models\SuporteDemanda;
use App\Models\User;
use App\Traits\Livewire\Suporte\ComUploadsSuporte;
use App\Traits\Livewire\Suporte\RegistraErrosSuporte;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class DemandaDetalhe extends Component
{
    use ComUploadsSuporte;
    use RegistraErrosSuporte;

    #[Locked]
    public ?int $demandaId = null;

    public SuporteDemandaForm $form;

    public string $status = 'aberta';

    #[Locked]
    public int $pendenciaExibida = 0;

    public function mount(?SuporteDemanda $demanda = null): void
    {
        SuporteDemanda::autorizarAcesso(auth()->user());
        $this->demandaId = $demanda?->id;
        $this->carregarFormulario($demanda);
    }

    protected function demanda(): ?SuporteDemanda
    {
        return $this->demandaId ? SuporteDemanda::query()->findOrFail($this->demandaId) : null;
    }

    protected function carregarFormulario(?SuporteDemanda $demanda): void
    {
        $this->form->setDemanda($demanda);
        $this->status = $demanda?->status->value ?? 'aberta';
        $this->anexos = $demanda?->arquivos()->where('tipo', 'anexo')->pluck('uid')->all() ?? [];
        $this->rascunho = SuporteArquivo::novoRascunho($demanda);
    }

    public function salvar(SalvarDemandaSuporteAction $action): mixed
    {
        SuporteDemanda::autorizarAcesso(auth()->user());
        $this->form->validate();
        $this->validate(['status' => ['required', Rule::enum(SuporteStatus::class)]]);
        $demanda = $action->execute(auth()->user(), $this->demanda(), $this->form->dados(), $this->rascunho, $this->anexos, SuporteStatus::from($this->status));
        session()->flash('success', 'Demanda salva com sucesso.');

        return $this->redirectRoute('suporte-insert', ['demanda' => $demanda->uid]);
    }

    public function resolver(): void
    {
        $this->demanda()?->resolverPendencia(auth()->user(), $this->pendenciaExibida);
    }

    public function excluir(): mixed
    {
        $this->demanda()?->excluir(auth()->user());
        session()->flash('success', 'Demanda excluída.');

        return $this->redirectRoute('suporte-index');
    }

    public function excluirAtividade(string $uid): void
    {
        SuporteDemanda::autorizarAcesso(auth()->user());
        $this->demanda()?->atividades()->where('uid', $uid)->firstOrFail()->excluir(auth()->user());
        $this->dispatch('suporte-atualizado');
    }

    #[On('suporte-atualizado')]
    public function atualizar(): void
    {
        $this->form->participantes = array_values(array_unique(array_merge(
            $this->form->participantes,
            $this->demanda()?->participantes()->pluck('users.id')->all() ?? [],
        )));
    }

    public function render(): View
    {
        SuporteDemanda::autorizarAcesso(auth()->user());
        $demanda = $this->demanda();
        $demanda?->load(['autor', 'participantes', 'arquivos', 'atividades' => fn ($query) => $query->whereNull('parent_id')->orderBy('created_at')->orderBy('id'),
            'atividades.owner', 'atividades.arquivos', 'atividades.respostas.owner', 'atividades.respostas.arquivos']);
        $this->pendenciaExibida = $demanda?->pendencia_versao ?? 0;

        return view('livewire.suporte.demanda-detalhe', [
            'demanda' => $demanda,
            'classificacoes' => SuporteClassificacao::cases(),
            'statuses' => SuporteStatus::cases(),
            'editando' => ! $demanda?->status->encerrada(),
            'usuarios' => ! $demanda?->status->encerrada() ? User::query()->whereHas('permissions', fn ($query) => $query->whereIn('permission', ['admin', 'funcionario']))->orderBy('name')->get(['id', 'name']) : collect(),
            'arquivosSelecionados' => ! $demanda?->status->encerrada() ? $this->anexosSelecionados() : collect(),
        ]);
    }
}

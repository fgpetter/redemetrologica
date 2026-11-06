<?php

namespace App\Livewire\Interlab;

use App\Actions\Interlab\ExcluirDespesaInterlabAction;
use App\Models\InterlabDespesaLancamento;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class DespesaLista extends Component
{
    #[Locked]
    public int $agendaInterlabId;

    public function mount(int $agendaInterlabId): void
    {
        $this->agendaInterlabId = $agendaInterlabId;
    }

    #[On('despesa-salva')]
    #[On('despesa-deletada')]
    public function refreshList(): void
    {
        //
    }

    public function editarLancamento(int $lancamentoId): void
    {
        $this->dispatch('abrir-despesa-modal', lancamentoId: $lancamentoId);
    }

    public function deletarLancamento(int $lancamentoId, ExcluirDespesaInterlabAction $excluirDespesa): void
    {
        if (! $excluirDespesa->execute($this->agendaInterlabId, $lancamentoId)) {
            return;
        }

        session()->flash('warning', 'Lançamento de despesa removido.');
        $this->dispatch('despesa-deletada');
    }

    public function render(): View
    {
        $lancamentos = InterlabDespesaLancamento::query()
            ->where('agenda_interlab_id', $this->agendaInterlabId)
            ->with(['fornecedor.pessoa', 'itens', 'avaliacao'])
            ->orderBy('id')
            ->get()
            ->map(function (InterlabDespesaLancamento $lancamento) {
                return [
                    'id' => $lancamento->id,
                    'fornecedor_nome' => $lancamento->fornecedor?->pessoa?->nome_razao ?? '—',
                    'ultima_data_compra' => $lancamento->itens->pluck('data_compra')->filter()->max(),
                    'media_avaliacao' => $lancamento->avaliacao?->media,
                    'total' => $lancamento->itens->sum('total'),
                ];
            });

        $totalGeral = $lancamentos->sum('total');

        return view('livewire.interlab.despesa-lista', [
            'lancamentos' => $lancamentos,
            'totalGeral' => $totalGeral,
        ]);
    }
}

<?php

namespace App\Livewire\Interlab;

use App\Models\InterlabDespesaLancamento;
use Livewire\Attributes\On;
use Livewire\Component;

class DespesaLista extends Component
{
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

    public function deletarLancamento(int $lancamentoId): void
    {
        $lancamento = InterlabDespesaLancamento::query()
            ->where('agenda_interlab_id', $this->agendaInterlabId)
            ->find($lancamentoId);

        if (! $lancamento) {
            return;
        }

        $lancamento->itens()->get()->each->delete();
        $lancamento->avaliacao()->delete();
        $lancamento->delete();

        session()->flash('warning', 'Lançamento de despesa removido.');
        $this->dispatch('despesa-deletada');
    }

    public function render()
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

<?php

namespace App\Livewire\Interlab;

use App\Models\InterlabDespesa;
use Illuminate\Support\Collection;
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

    public function editarFornecedor(int $fornecedorId): void
    {
        $this->dispatch('abrir-despesa-modal', fornecedorId: $fornecedorId);
    }

    public function deletarPorFornecedor(int $fornecedorId): void
    {
        InterlabDespesa::where('agenda_interlab_id', $this->agendaInterlabId)
            ->where('fornecedor_id', $fornecedorId)
            ->get()
            ->each->delete();
        session()->flash('warning', 'Despesas do fornecedor removidas.');
        $this->dispatch('despesa-deletada');
    }

    public function render()
    {
        $despesas = InterlabDespesa::where('agenda_interlab_id', $this->agendaInterlabId)
            ->with(['interlabFornecedor.pessoa'])
            ->orderBy('fornecedor_id')
            ->orderBy('id')
            ->get();

        $agrupadas = $despesas->groupBy('fornecedor_id')->map(function (Collection $itens, $fornecedorId) {
            $total = $itens->sum('total');
            $fornecedorNome = $itens->first()?->interlabFornecedor?->pessoa?->nome_razao ?? '—';

            return [
                'fornecedor_id' => (int) $fornecedorId,
                'fornecedor_nome' => $fornecedorNome,
                'total' => $total,
            ];
        })->filter(fn ($g) => $g['fornecedor_id'] > 0)->values();

        $totalGeral = $despesas->sum('total');

        return view('livewire.interlab.despesa-lista', [
            'agrupadas' => $agrupadas,
            'totalGeral' => $totalGeral,
        ]);
    }
}

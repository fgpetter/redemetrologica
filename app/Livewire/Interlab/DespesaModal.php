<?php

namespace App\Livewire\Interlab;

use App\Enums\FornecedorArea;
use App\Models\Fornecedor;
use App\Models\InterlabDespesa;
use Livewire\Attributes\On;
use Livewire\Component;

class DespesaModal extends Component
{
    public int $agendaInterlabId;

    public ?int $despesaEditandoId = null;

    public bool $showModal = false;

    public string $fornecedorId = '';

    public ?int $editingIndex = 0;

    public array $produtos = [
        [
            'material_servico' => '',
            'fabricante' => '',
            'cod_fabricante' => '',
            'quantidade' => '',
            'valor' => '',
            'lote' => '',
            'validade' => '',
            'data_compra' => '',
        ],
    ];

    public function mount(int $agendaInterlabId): void
    {
        $this->agendaInterlabId = $agendaInterlabId;
    }

    #[On('abrir-despesa-modal')]
    public function abrirDespesaModal(?int $fornecedorId = null): void
    {
        if ($fornecedorId !== null) {
            $this->abrirParaEditar($fornecedorId);
        } else {
            $this->abrirParaCriar();
        }
    }

    public function abrirParaCriar(): void
    {
        $this->resetValidation();
        $this->fornecedorId = '';
        $this->despesaEditandoId = null;
        $this->editingIndex = 0;
        $this->produtos = [
            [
                'material_servico' => '',
                'fabricante' => '',
                'cod_fabricante' => '',
                'quantidade' => '',
                'valor' => '',
                'lote' => '',
                'validade' => '',
                'data_compra' => '',
            ],
        ];
        $this->showModal = true;
    }

    public function abrirParaEditar(int $fornecedorId): void
    {
        $despesas = InterlabDespesa::where('agenda_interlab_id', $this->agendaInterlabId)
            ->where('fornecedor_id', $fornecedorId)
            ->orderBy('id')
            ->get();

        if ($despesas->isEmpty()) {
            $this->abrirParaCriar();

            return;
        }

        $this->fornecedorId = (string) $fornecedorId;
        $this->despesaEditandoId = $despesas->first()->id;
        $this->produtos = $despesas->map(fn (InterlabDespesa $d) => [
            'material_servico' => $d->material_servico ?? '',
            'fabricante' => $d->fabricante ?? '',
            'cod_fabricante' => $d->cod_fabricante ?? '',
            'quantidade' => $d->quantidade !== null ? (string) $d->quantidade : '',
            'valor' => $d->valor !== null ? number_format($d->valor, 2, ',', '.') : '',
            'lote' => $d->lote ?? '',
            'validade' => $d->validade?->format('Y-m-d') ?? '',
            'data_compra' => $d->data_compra?->format('Y-m-d') ?? '',
        ])->toArray();
        $this->editingIndex = null;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function editarProduto(int $index): void
    {
        $this->editingIndex = $index;
    }

    public function adicionarProduto(): void
    {
        $this->produtos[] = [
            'material_servico' => '',
            'fabricante' => '',
            'cod_fabricante' => '',
            'quantidade' => '',
            'valor' => '',
            'lote' => '',
            'validade' => '',
            'data_compra' => '',
        ];
        $this->editingIndex = count($this->produtos) - 1;
    }

    public function removerProduto(int $index): void
    {
        if (count($this->produtos) <= 1) {
            return;
        }
        array_splice($this->produtos, $index, 1);

        if ($this->editingIndex === $index) {
            $this->editingIndex = null;
        } elseif ($this->editingIndex !== null && $this->editingIndex > $index) {
            $this->editingIndex--;
        }
    }

    public function salvar(): void
    {
        $this->validate([
            'fornecedorId' => ['required', 'integer', 'exists:fornecedores,id'],
            'produtos' => ['required', 'array', 'min:1'],
            'produtos.*.material_servico' => ['required', 'string', 'max:255'],
            'produtos.*.quantidade' => ['nullable', 'numeric', 'min:0'],
            'produtos.*.valor' => ['nullable', 'string'],
        ], [
            'fornecedorId.required' => 'Selecione o fornecedor.',
            'produtos.*.material_servico.required' => 'Material/Serviço é obrigatório.',
        ]);

        $fornecedorId = (int) $this->fornecedorId;

        $produtosValidados = [];
        foreach ($this->produtos as $p) {
            $qtd = isset($p['quantidade']) && $p['quantidade'] !== '' ? (float) str_replace(',', '.', $p['quantidade']) : 0;
            $valor = isset($p['valor']) && $p['valor'] !== '' ? (float) formataMoeda($p['valor']) : null;

            $produtosValidados[] = [
                'material_servico' => $p['material_servico'] ?? '',
                'fabricante' => $p['fabricante'] ?? '',
                'cod_fabricante' => $p['cod_fabricante'] ?? '',
                'quantidade' => $qtd ?: null,
                'valor' => $valor,
                'total' => $valor !== null && $qtd ? $valor * $qtd : null,
                'lote' => $p['lote'] ?? null,
                'validade' => ! empty($p['validade']) ? $p['validade'] : null,
                'data_compra' => ! empty($p['data_compra']) ? $p['data_compra'] : null,
            ];
        }

        if ($this->despesaEditandoId !== null) {
            $existentes = InterlabDespesa::where('agenda_interlab_id', $this->agendaInterlabId)
                ->where('fornecedor_id', $fornecedorId)
                ->orderBy('id')
                ->get();
            $ids = $existentes->pluck('id')->toArray();
            foreach ($produtosValidados as $i => $data) {
                $data['agenda_interlab_id'] = $this->agendaInterlabId;
                $data['fornecedor_id'] = $fornecedorId;
                if (isset($ids[$i])) {
                    InterlabDespesa::where('id', $ids[$i])->update($data);
                } else {
                    InterlabDespesa::create($data);
                }
            }
            if (count($produtosValidados) < count($ids)) {
                foreach (array_slice($ids, count($produtosValidados)) as $id) {
                    InterlabDespesa::where('id', $id)->delete();
                }
            }
        } else {
            foreach ($produtosValidados as $data) {
                $data['agenda_interlab_id'] = $this->agendaInterlabId;
                $data['fornecedor_id'] = $fornecedorId;
                InterlabDespesa::create($data);
            }
        }

        $this->dispatch('despesa-salva');
        session()->flash('success', 'Despesa(s) salva(s) com sucesso.');
        $this->fechar();
    }

    public function deletar(int $despesaId): void
    {
        $despesa = InterlabDespesa::where('agenda_interlab_id', $this->agendaInterlabId)
            ->where('id', $despesaId)
            ->first();

        if ($despesa) {
            $despesa->delete();
            $this->dispatch('despesa-deletada');
            session()->flash('warning', 'Despesa removida.');
        }
        $this->fechar();
    }

    public function deletarPorFornecedor(int $fornecedorId): void
    {
        InterlabDespesa::where('agenda_interlab_id', $this->agendaInterlabId)
            ->where('fornecedor_id', $fornecedorId)
            ->delete();
        $this->dispatch('despesa-deletada');
        session()->flash('warning', 'Despesas do fornecedor removidas.');
        $this->fechar();
    }

    public function fechar(): void
    {
        $this->showModal = false;
    }

    public function render()
    {
        $fornecedores = Fornecedor::with('pessoa')
            ->whereHas('areas', fn ($q) => $q->where('area', FornecedorArea::PEP))
            ->orderBy('id')
            ->get();

        return view('livewire.interlab.despesa-modal', [
            'fornecedores' => $fornecedores,
        ]);
    }
}

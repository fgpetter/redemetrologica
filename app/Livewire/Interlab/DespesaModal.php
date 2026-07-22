<?php

namespace App\Livewire\Interlab;

use App\Actions\Interlab\SyncFornecedorAvaliacaoAction;
use App\Enums\FornecedorArea;
use App\Models\Fornecedor;
use App\Models\InterlabDespesa;
use App\Models\InterlabDespesaLancamento;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class DespesaModal extends Component
{
    public int $agendaInterlabId;

    public ?int $lancamentoId = null;

    public bool $showModal = false;

    public string $fornecedorId = '';

    public ?int $editingIndex = 0;

    public string $avaliacaoCusto = '';

    public string $avaliacaoTempo = '';

    public string $avaliacaoQualidade = '';

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
    public function abrirDespesaModal(?int $lancamentoId = null): void
    {
        if ($lancamentoId !== null) {
            $this->abrirParaEditar($lancamentoId);
        } else {
            $this->abrirParaCriar();
        }
    }

    public function abrirParaCriar(): void
    {
        $this->resetValidation();
        $this->fornecedorId = '';
        $this->lancamentoId = null;
        $this->editingIndex = 0;
        $this->avaliacaoCusto = '';
        $this->avaliacaoTempo = '';
        $this->avaliacaoQualidade = '';
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

    public function abrirParaEditar(int $lancamentoId): void
    {
        $lancamento = InterlabDespesaLancamento::query()
            ->where('agenda_interlab_id', $this->agendaInterlabId)
            ->with(['itens' => fn ($q) => $q->orderBy('id'), 'avaliacao'])
            ->find($lancamentoId);

        if (! $lancamento) {
            $this->abrirParaCriar();

            return;
        }

        $this->lancamentoId = $lancamento->id;
        $this->fornecedorId = (string) $lancamento->fornecedor_id;
        $this->produtos = $lancamento->itens->map(fn (InterlabDespesa $d) => [
            'material_servico' => $d->material_servico ?? '',
            'fabricante' => $d->fabricante ?? '',
            'cod_fabricante' => $d->cod_fabricante ?? '',
            'quantidade' => $d->quantidade !== null ? (string) $d->quantidade : '',
            'valor' => $d->valor !== null ? number_format($d->valor, 2, ',', '.') : '',
            'lote' => $d->lote ?? '',
            'validade' => $d->validade?->format('Y-m-d') ?? '',
            'data_compra' => $d->data_compra?->format('Y-m-d') ?? '',
        ])->values()->toArray();

        if ($this->produtos === []) {
            $this->produtos = [[
                'material_servico' => '',
                'fabricante' => '',
                'cod_fabricante' => '',
                'quantidade' => '',
                'valor' => '',
                'lote' => '',
                'validade' => '',
                'data_compra' => '',
            ]];
        }

        $avaliacao = $lancamento->avaliacao;
        $this->avaliacaoCusto = $avaliacao?->custo !== null ? (string) $avaliacao->custo : '';
        $this->avaliacaoTempo = $avaliacao?->tempo !== null ? (string) $avaliacao->tempo : '';
        $this->avaliacaoQualidade = $avaliacao?->qualidade !== null ? (string) $avaliacao->qualidade : '';
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

        $lancamento = DB::transaction(function () use ($fornecedorId, $produtosValidados) {
            if ($this->lancamentoId !== null) {
                $lancamento = InterlabDespesaLancamento::query()
                    ->where('agenda_interlab_id', $this->agendaInterlabId)
                    ->findOrFail($this->lancamentoId);

                $lancamento->update(['fornecedor_id' => $fornecedorId]);
            } else {
                $lancamento = InterlabDespesaLancamento::query()->create([
                    'agenda_interlab_id' => $this->agendaInterlabId,
                    'fornecedor_id' => $fornecedorId,
                ]);
            }

            $existentes = $lancamento->itens()->orderBy('id')->get();
            $ids = $existentes->pluck('id')->toArray();

            foreach ($produtosValidados as $i => $data) {
                $data['interlab_despesa_lancamento_id'] = $lancamento->id;

                if (isset($ids[$i])) {
                    InterlabDespesa::where('id', $ids[$i])->update($data);
                } else {
                    InterlabDespesa::create($data);
                }
            }

            if (count($produtosValidados) < count($ids)) {
                foreach (array_slice($ids, count($produtosValidados)) as $id) {
                    InterlabDespesa::find($id)?->delete();
                }
            }

            app(SyncFornecedorAvaliacaoAction::class)->sync(
                $lancamento->fresh(),
                $this->avaliacaoComoInt($this->avaliacaoCusto),
                $this->avaliacaoComoInt($this->avaliacaoTempo),
                $this->avaliacaoComoInt($this->avaliacaoQualidade),
            );

            return $lancamento;
        });

        $this->lancamentoId = $lancamento->id;
        $this->dispatch('despesa-salva');
        session()->flash('success', 'Despesa(s) salva(s) com sucesso.');
        $this->fechar();
    }

    public function deletarLancamento(): void
    {
        if ($this->lancamentoId === null) {
            return;
        }

        $lancamento = InterlabDespesaLancamento::query()
            ->where('agenda_interlab_id', $this->agendaInterlabId)
            ->find($this->lancamentoId);

        if ($lancamento) {
            $lancamento->itens()->get()->each->delete();
            $lancamento->avaliacao()->delete();
            $lancamento->delete();
            $this->dispatch('despesa-deletada');
            session()->flash('warning', 'Lançamento de despesa removido.');
        }

        $this->fechar();
    }

    public function fechar(): void
    {
        $this->showModal = false;
    }

    private function avaliacaoComoInt(string $valor): ?int
    {
        if ($valor === '') {
            return null;
        }

        return (int) $valor;
    }

    public function render()
    {
        $fornecedores = $this->showModal
            ? Fornecedor::with('pessoa')
                ->whereHas('areas', fn ($q) => $q->where('area', FornecedorArea::PEP))
                ->orderBy('id')
                ->get()
            : collect();

        return view('livewire.interlab.despesa-modal', [
            'fornecedores' => $fornecedores,
        ]);
    }
}

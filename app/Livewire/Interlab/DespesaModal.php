<?php

namespace App\Livewire\Interlab;

use App\Actions\Interlab\ExcluirDespesaInterlabAction;
use App\Actions\Interlab\SalvarDespesaInterlabAction;
use App\Enums\FornecedorArea;
use App\Livewire\Forms\DespesaInterlabForm;
use App\Models\Fornecedor;
use App\Models\InterlabDespesa;
use App\Models\InterlabDespesaLancamento;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class DespesaModal extends Component
{
    #[Locked]
    public int $agendaInterlabId;

    #[Locked]
    public ?int $lancamentoId = null;

    public DespesaInterlabForm $form;

    public bool $showModal = false;

    public string $fornecedorId = '';

    public string $dataCompra = '';

    public string $tipo = '';

    public string $especificacao = '';

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
            'nota_fiscal' => '',
        ],
    ];

    public function mount(int $agendaInterlabId): void
    {
        $this->agendaInterlabId = $agendaInterlabId;
    }

    #[On('abrir-despesa-modal')]
    public function abrirDespesaModal(?int $lancamentoId = null, ?int $fornecedorId = null): void
    {
        if ($lancamentoId !== null) {
            $this->abrirParaEditar($lancamentoId);
        } else {
            $this->abrirParaCriar($fornecedorId);
        }
    }

    public function abrirParaCriar(?int $fornecedorId = null): void
    {
        $this->resetValidation();
        $this->fornecedorId = $fornecedorId === null ? '' : (string) $fornecedorId;
        $this->dataCompra = '';
        $this->tipo = '';
        $this->especificacao = '';
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
                'nota_fiscal' => '',
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
        $this->dataCompra = $lancamento->data_compra?->format('Y-m-d') ?? '';
        $this->tipo = $lancamento->tipo ?? '';
        $this->especificacao = $lancamento->especificacao ?? '';
        $this->produtos = $lancamento->itens->map(fn (InterlabDespesa $d) => [
            'id' => $d->id,
            'material_servico' => $d->material_servico ?? '',
            'fabricante' => $d->fabricante ?? '',
            'cod_fabricante' => $d->cod_fabricante ?? '',
            'quantidade' => $d->quantidade !== null ? (string) $d->quantidade : '',
            'valor' => $d->valor !== null ? number_format($d->valor, 2, ',', '.') : '',
            'lote' => $d->lote ?? '',
            'validade' => $d->validade?->format('Y-m-d') ?? '',
            'nota_fiscal' => $d->nota_fiscal ?? '',
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
                'nota_fiscal' => '',
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
            'nota_fiscal' => '',
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

    public function salvar(SalvarDespesaInterlabAction $salvarDespesa): void
    {
        $dados = $this->validate($this->form->rules($this->lancamentoId === null), $this->form->messages());

        $lancamento = $salvarDespesa->execute($this->agendaInterlabId, $this->lancamentoId, $dados);

        $this->lancamentoId = $lancamento->id;
        $this->dispatch('despesa-salva');
        session()->flash('success', 'Despesa(s) salva(s) com sucesso.');
        $this->fechar();
    }

    public function deletarLancamento(ExcluirDespesaInterlabAction $excluirDespesa): void
    {
        if ($this->lancamentoId === null) {
            return;
        }

        if ($excluirDespesa->execute($this->agendaInterlabId, $this->lancamentoId)) {
            $this->dispatch('despesa-deletada');
            session()->flash('warning', 'Lançamento de despesa removido.');
        }

        $this->fechar();
    }

    public function fechar(): void
    {
        $this->showModal = false;
    }

    public function render(): View
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

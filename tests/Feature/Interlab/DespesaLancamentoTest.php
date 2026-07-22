<?php

use App\Enums\FornecedorArea;
use App\Livewire\Interlab\DespesaLista;
use App\Livewire\Interlab\DespesaModal;
use App\Models\AgendaInterlab;
use App\Models\Fornecedor;
use App\Models\FornecedorArea as FornecedorAreaModel;
use App\Models\FornecedorAvaliacao;
use App\Models\Interlab;
use App\Models\InterlabDespesa;
use App\Models\InterlabDespesaLancamento;
use Database\Factories\PessoaFactory;
use Livewire\Livewire;

function criarAgendaInterlabParaDespesa(): AgendaInterlab
{
    $interlab = Interlab::query()->create([
        'nome' => 'Interlab Despesas',
        'tipo' => 'INTERLABORATORIAL',
    ]);

    return AgendaInterlab::query()->create([
        'interlab_id' => $interlab->id,
        'status' => 'CONFIRMADO',
        'inscricao' => true,
        'ano_referencia' => (int) now()->format('Y'),
    ]);
}

function criarFornecedorPep(string $nome = 'Fornecedor PEP'): Fornecedor
{
    $pessoa = PessoaFactory::new()->create([
        'nome_razao' => $nome,
    ]);

    $fornecedor = Fornecedor::query()->create([
        'pessoa_id' => $pessoa->id,
    ]);

    FornecedorAreaModel::query()->create([
        'fornecedor_id' => $fornecedor->id,
        'area' => FornecedorArea::PEP,
    ]);

    return $fornecedor;
}

test('cria lancamento com varios itens e avaliacao', function () {
    $agenda = criarAgendaInterlabParaDespesa();
    $fornecedor = criarFornecedorPep();

    Livewire::test(DespesaModal::class, ['agendaInterlabId' => $agenda->id])
        ->call('abrirParaCriar')
        ->set('fornecedorId', (string) $fornecedor->id)
        ->set('produtos', [
            [
                'material_servico' => 'Reagente A',
                'fabricante' => 'Fab',
                'cod_fabricante' => 'A1',
                'quantidade' => '2',
                'valor' => '10,00',
                'lote' => 'L1',
                'validade' => '2026-12-31',
                'data_compra' => '2026-07-01',
            ],
            [
                'material_servico' => 'Reagente B',
                'fabricante' => 'Fab',
                'cod_fabricante' => 'B1',
                'quantidade' => '1',
                'valor' => '5,00',
                'lote' => 'L2',
                'validade' => '',
                'data_compra' => '2026-07-02',
            ],
        ])
        ->set('avaliacaoCusto', '4')
        ->set('avaliacaoTempo', '5')
        ->set('avaliacaoQualidade', '3')
        ->call('salvar')
        ->assertHasNoErrors();

    $lancamento = InterlabDespesaLancamento::query()->first();

    expect($lancamento)->not->toBeNull()
        ->and($lancamento->fornecedor_id)->toBe($fornecedor->id)
        ->and($lancamento->itens()->count())->toBe(2)
        ->and((float) $lancamento->itens()->sum('total'))->toBe(25.0)
        ->and($lancamento->avaliacao)->not->toBeNull()
        ->and((float) $lancamento->avaliacao->media)->toBe(4.0);
});

test('permite dois lancamentos do mesmo fornecedor sem mesclar', function () {
    $agenda = criarAgendaInterlabParaDespesa();
    $fornecedor = criarFornecedorPep();

    Livewire::test(DespesaModal::class, ['agendaInterlabId' => $agenda->id])
        ->call('abrirParaCriar')
        ->set('fornecedorId', (string) $fornecedor->id)
        ->set('produtos.0.material_servico', 'Item 1')
        ->set('produtos.0.quantidade', '1')
        ->set('produtos.0.valor', '10,00')
        ->set('avaliacaoCusto', '5')
        ->set('avaliacaoTempo', '5')
        ->set('avaliacaoQualidade', '5')
        ->call('salvar')
        ->assertHasNoErrors();

    Livewire::test(DespesaModal::class, ['agendaInterlabId' => $agenda->id])
        ->call('abrirParaCriar')
        ->set('fornecedorId', (string) $fornecedor->id)
        ->set('produtos.0.material_servico', 'Item 2')
        ->set('produtos.0.quantidade', '1')
        ->set('produtos.0.valor', '20,00')
        ->call('salvar')
        ->assertHasNoErrors();

    expect(InterlabDespesaLancamento::query()->count())->toBe(2)
        ->and(InterlabDespesa::query()->count())->toBe(2)
        ->and(FornecedorAvaliacao::query()->count())->toBe(1);

    Livewire::test(DespesaLista::class, ['agendaInterlabId' => $agenda->id])
        ->assertViewHas('lancamentos', fn ($lancamentos) => $lancamentos->count() === 2);
});

test('preserva avaliacao ao editar lancamento sem alterar notas', function () {
    $agenda = criarAgendaInterlabParaDespesa();
    $fornecedor = criarFornecedorPep();

    $lancamento = InterlabDespesaLancamento::query()->create([
        'agenda_interlab_id' => $agenda->id,
        'fornecedor_id' => $fornecedor->id,
    ]);

    InterlabDespesa::query()->create([
        'interlab_despesa_lancamento_id' => $lancamento->id,
        'material_servico' => 'Item original',
        'quantidade' => 1,
        'valor' => 10,
        'total' => 10,
    ]);

    FornecedorAvaliacao::query()->create([
        'interlab_despesa_lancamento_id' => $lancamento->id,
        'fornecedor_id' => $fornecedor->id,
        'custo' => 4,
        'tempo' => 4,
        'qualidade' => 4,
        'media' => 4,
    ]);

    Livewire::test(DespesaModal::class, ['agendaInterlabId' => $agenda->id])
        ->call('abrirParaEditar', $lancamento->id)
        ->set('produtos.0.material_servico', 'Item atualizado')
        ->set('avaliacaoCusto', '4')
        ->set('avaliacaoTempo', '4')
        ->set('avaliacaoQualidade', '4')
        ->call('salvar')
        ->assertHasNoErrors();

    $lancamento->refresh();

    expect($lancamento->itens()->first()->material_servico)->toBe('Item atualizado')
        ->and(FornecedorAvaliacao::query()->count())->toBe(1)
        ->and((float) $lancamento->avaliacao->media)->toBe(4.0);
});

test('lista uma linha por lancamento', function () {
    $agenda = criarAgendaInterlabParaDespesa();
    $fornecedor = criarFornecedorPep('Fornecedor Lista');

    $primeiro = InterlabDespesaLancamento::query()->create([
        'agenda_interlab_id' => $agenda->id,
        'fornecedor_id' => $fornecedor->id,
    ]);
    InterlabDespesa::query()->create([
        'interlab_despesa_lancamento_id' => $primeiro->id,
        'material_servico' => 'A',
        'total' => 10,
        'data_compra' => '2026-07-01',
    ]);

    $segundo = InterlabDespesaLancamento::query()->create([
        'agenda_interlab_id' => $agenda->id,
        'fornecedor_id' => $fornecedor->id,
    ]);
    InterlabDespesa::query()->create([
        'interlab_despesa_lancamento_id' => $segundo->id,
        'material_servico' => 'B',
        'total' => 30,
        'data_compra' => '2026-07-10',
    ]);

    Livewire::test(DespesaLista::class, ['agendaInterlabId' => $agenda->id])
        ->assertSee('Fornecedor Lista')
        ->assertSee('R$ 10,00')
        ->assertSee('R$ 30,00')
        ->assertSee('R$ 40,00')
        ->assertViewHas('lancamentos', fn ($lancamentos) => $lancamentos->count() === 2);
});

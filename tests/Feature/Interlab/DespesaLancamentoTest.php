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
use App\Models\User;
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
        ->set('dataCompra', '2026-07-01')
        ->set('tipo', 'produto')
        ->set('especificacao', 'Reagentes')
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
        ->set('dataCompra', '2026-07-01')
        ->set('tipo', 'produto')
        ->set('especificacao', 'Reagentes')
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
        ->set('dataCompra', '2026-07-01')
        ->set('tipo', 'produto')
        ->set('especificacao', 'Reagentes')
        ->set('produtos.0.material_servico', 'Item 2')
        ->set('produtos.0.quantidade', '1')
        ->set('produtos.0.valor', '20,00')
        ->call('salvar')
        ->assertHasNoErrors();

    expect(InterlabDespesaLancamento::query()->count())->toBe(2)
        ->and(InterlabDespesa::query()->count())->toBe(2)
        ->and(FornecedorAvaliacao::query()->count())->toBe(1);

    Livewire::test(DespesaLista::class, ['agendaInterlabId' => $agenda->id])
        ->assertViewHas('lancamentos', fn ($lancamentos) => $lancamentos->count() === 1 && $lancamentos->first()->count() === 2);
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
        ->assertViewHas('lancamentos', fn ($lancamentos) => $lancamentos->count() === 1 && $lancamentos->first()->count() === 2);
});

test('informa separadamente o campo de classificacao que falta', function () {
    $agenda = criarAgendaInterlabParaDespesa();
    $fornecedor = criarFornecedorPep('Fornecedor Classificação');

    foreach ([
        ['tipo' => 'produto', 'especificacao' => null],
        ['tipo' => null, 'especificacao' => 'Ensaio de água'],
        ['tipo' => null, 'especificacao' => null],
        ['tipo' => 'servico', 'especificacao' => 'Auditoria'],
        ['tipo' => 'servico', 'especificacao' => '0'],
    ] as $classificacao) {
        $agenda->despesaLancamentos()->create([
            'fornecedor_id' => $fornecedor->id,
            ...$classificacao,
        ]);
    }

    Livewire::test(DespesaLista::class, ['agendaInterlabId' => $agenda->id])
        ->assertSee('Produto — Especificação pendente')
        ->assertSee('Tipo pendente — Ensaio de água')
        ->assertSee('Sem tipo - Sem Especificação')
        ->assertSee('Serviço — Auditoria')
        ->assertSee('Serviço — 0')
        ->assertDontSee('Produto — Classificação pendente');
});

test('valida campos da compra e avaliação completa na escala de zero a dez', function () {
    $agenda = criarAgendaInterlabParaDespesa();
    $fornecedor = criarFornecedorPep();
    Livewire::test(DespesaModal::class, ['agendaInterlabId' => $agenda->id])
        ->call('abrirParaCriar', $fornecedor->id)
        ->set('produtos.0.material_servico', 'Item')
        ->call('salvar')->assertHasErrors(['dataCompra', 'tipo', 'especificacao'])
        ->set('dataCompra', '2026-07-01')->set('tipo', 'servico')->set('especificacao', 'Ensaio')
        ->set('avaliacaoCusto', '11')->call('salvar')->assertHasErrors(['avaliacaoCusto'])
        ->set('avaliacaoCusto', '10')->call('salvar')->assertHasErrors(['avaliacao'])
        ->set('avaliacaoTempo', '0')->set('avaliacaoQualidade', '10')
        ->set('produtos.0.nota_fiscal', 'NF 123')->call('salvar')->assertHasNoErrors();
    $compra = InterlabDespesaLancamento::first();
    expect($compra->tipo)->toBe('servico')->and($compra->itens->first()->nota_fiscal)->toBe('NF 123')
        ->and((float) $compra->avaliacao->media)->toBe(6.67);
});

test('preserva data histórica do item restante ao remover primeiro item', function () {
    $agenda = criarAgendaInterlabParaDespesa();
    $fornecedor = criarFornecedorPep();
    $compra = InterlabDespesaLancamento::create(['agenda_interlab_id' => $agenda->id, 'fornecedor_id' => $fornecedor->id]);
    $primeiro = $compra->itens()->create(['material_servico' => 'Primeiro', 'data_compra' => '2026-01-01']);
    $segundo = $compra->itens()->create(['material_servico' => 'Segundo', 'data_compra' => '2026-02-01']);
    Livewire::test(DespesaLista::class, ['agendaInterlabId' => $agenda->id])->assertSee('Datas variadas')->assertSee('Sem tipo - Sem Especificação');
    Livewire::test(DespesaModal::class, ['agendaInterlabId' => $agenda->id])
        ->call('abrirParaEditar', $compra->id)->call('removerProduto', 0)->call('salvar')->assertHasNoErrors();
    expect($primeiro->fresh()->trashed())->toBeTrue()
        ->and($segundo->fresh()->material_servico)->toBe('Segundo')
        ->and($segundo->fresh()->data_compra->format('Y-m-d'))->toBe('2026-02-01');
});

test('registra avaliador somente quando as notas mudam', function () {
    $agenda = criarAgendaInterlabParaDespesa();
    $fornecedor = criarFornecedorPep();
    $autor = User::factory()->create();
    $outro = User::factory()->create();
    $compra = InterlabDespesaLancamento::create(['agenda_interlab_id' => $agenda->id, 'fornecedor_id' => $fornecedor->id]);
    $compra->itens()->create(['material_servico' => 'Item']);
    $this->actingAs($autor);
    Livewire::test(DespesaModal::class, ['agendaInterlabId' => $agenda->id])
        ->call('abrirParaEditar', $compra->id)->set('avaliacaoCusto', '10')->set('avaliacaoTempo', '8')
        ->set('avaliacaoQualidade', '6')->call('salvar')->assertHasNoErrors();
    expect($compra->avaliacao()->first()->avaliador->id)->toBe($autor->id);
    $this->actingAs($outro);
    Livewire::test(DespesaModal::class, ['agendaInterlabId' => $agenda->id])
        ->call('abrirParaEditar', $compra->id)->set('produtos.0.material_servico', 'Alterado')
        ->call('salvar')->assertHasNoErrors();
    expect($compra->avaliacao()->first()->avaliador_user_id)->toBe($autor->id);
    Livewire::test(DespesaModal::class, ['agendaInterlabId' => $agenda->id])
        ->call('abrirParaEditar', $compra->id)->set('avaliacaoCusto', '9')->call('salvar')->assertHasNoErrors();
    expect($compra->avaliacao()->first()->avaliador_user_id)->toBe($outro->id);
});

test('migra datas comuns e notas históricas sem modificar datas dos itens', function () {
    $agenda = criarAgendaInterlabParaDespesa();
    $fornecedor = criarFornecedorPep();
    $comum = InterlabDespesaLancamento::create(['agenda_interlab_id' => $agenda->id, 'fornecedor_id' => $fornecedor->id]);
    $variada = InterlabDespesaLancamento::create(['agenda_interlab_id' => $agenda->id, 'fornecedor_id' => $fornecedor->id]);
    $incompleta = InterlabDespesaLancamento::create(['agenda_interlab_id' => $agenda->id, 'fornecedor_id' => $fornecedor->id]);
    foreach ([[$comum, '2026-01-01'], [$comum, '2026-01-01'], [$variada, '2026-01-01'], [$variada, '2026-02-01'], [$incompleta, '2026-01-01'], [$incompleta, null]] as [$compra, $data]) {
        $compra->itens()->create(['material_servico' => 'Item', 'data_compra' => $data]);
    }
    $avaliacao = FornecedorAvaliacao::create(['interlab_despesa_lancamento_id' => $comum->id, 'fornecedor_id' => $fornecedor->id, 'custo' => 2, 'tempo' => 3, 'qualidade' => 5, 'media' => 3.33]);
    $parcial = FornecedorAvaliacao::create(['interlab_despesa_lancamento_id' => $variada->id, 'fornecedor_id' => $fornecedor->id, 'custo' => 1, 'tempo' => null, 'qualidade' => 4, 'media' => null]);
    $migration = require database_path('migrations/2026_11_07_000002_migrar_dados_compras_interlab.php');
    $migration->up();
    expect($comum->fresh()->data_compra->format('Y-m-d'))->toBe('2026-01-01')
        ->and($variada->fresh()->data_compra)->toBeNull()->and($incompleta->fresh()->data_compra)->toBeNull()
        ->and($variada->itens()->orderBy('id')->get()->last()->data_compra->format('Y-m-d'))->toBe('2026-02-01')
        ->and($avaliacao->fresh()->custo)->toBe(4)->and((float) $avaliacao->fresh()->media)->toBe(6.67)
        ->and($avaliacao->fresh()->avaliador_user_id)->toBeNull()->and($parcial->fresh()->custo)->toBe(2)
        ->and($parcial->fresh()->tempo)->toBeNull()->and($parcial->fresh()->media)->toBeNull();
    expect(fn () => $migration->down())->toThrow(RuntimeException::class);
});

test('permite classificar compra antiga com avaliação parcial sem alterar suas notas', function () {
    $agenda = criarAgendaInterlabParaDespesa();
    $fornecedor = criarFornecedorPep();
    $compra = $agenda->despesaLancamentos()->create(['fornecedor_id' => $fornecedor->id]);
    $compra->itens()->create(['material_servico' => 'Item histórico']);
    $avaliacao = $compra->avaliacao()->create(['fornecedor_id' => $fornecedor->id, 'custo' => 6]);

    Livewire::test(DespesaModal::class, ['agendaInterlabId' => $agenda->id])
        ->call('abrirParaEditar', $compra->id)
        ->set('tipo', 'produto')
        ->set('especificacao', '0')
        ->call('salvar')->assertHasNoErrors()
        ->call('abrirParaEditar', $compra->id)
        ->set('especificacao', '')
        ->call('salvar')->assertHasErrors(['especificacao']);

    expect($compra->fresh()->especificacao)->toBe('0')
        ->and($avaliacao->fresh()->custo)->toBe(6)
        ->and($avaliacao->fresh()->media)->toBeNull();
});

test('exige limpar a avaliação ao trocar fornecedor da compra', function () {
    $agenda = criarAgendaInterlabParaDespesa();
    $fornecedor = criarFornecedorPep();
    $outro = criarFornecedorPep('Outro fornecedor');
    $compra = $agenda->despesaLancamentos()->create(['fornecedor_id' => $fornecedor->id]);
    $compra->itens()->create(['material_servico' => 'Item']);
    $compra->avaliacao()->create(['fornecedor_id' => $fornecedor->id, 'custo' => 8, 'tempo' => 9, 'qualidade' => 10]);

    $modal = Livewire::test(DespesaModal::class, ['agendaInterlabId' => $agenda->id])
        ->call('abrirParaEditar', $compra->id)
        ->set('fornecedorId', (string) $outro->id)
        ->call('salvar')->assertHasErrors(['avaliacao']);

    expect($compra->fresh()->fornecedor_id)->toBe($fornecedor->id);

    $modal->set('avaliacaoCusto', '')->set('avaliacaoTempo', '')->set('avaliacaoQualidade', '')
        ->call('salvar')->assertHasNoErrors();

    expect($compra->fresh()->fornecedor_id)->toBe($outro->id)
        ->and($compra->avaliacao()->exists())->toBeFalse();
});

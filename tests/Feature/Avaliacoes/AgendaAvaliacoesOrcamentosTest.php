<?php

use App\Livewire\Avaliacoes\AgendaAvaliacoesOrcamentos;
use App\Models\AgendaAvaliacao;
use App\Models\AreaAtuacao;
use App\Models\AreaAvaliada;
use App\Models\Avaliador;
use App\Models\Laboratorio;
use App\Models\TipoAvaliacao;
use Database\Factories\PessoaFactory;
use Livewire\Livewire;

function criarAgendaAvaliacaoOrcamento(array $atributos = []): AgendaAvaliacao
{
    $pessoa = PessoaFactory::new()->create();
    $laboratorio = Laboratorio::query()->create([
        'pessoa_id' => $pessoa->id,
        'nome_laboratorio' => 'Lab Orçamento',
        'contato' => 'Contato',
        'telefone' => '11999999999',
        'email' => 'lab@example.com',
        'responsavel_tecnico' => 'Responsável',
    ]);

    $tipo = TipoAvaliacao::query()->create([
        'descricao' => 'Inicial',
    ]);

    return AgendaAvaliacao::query()->create(array_merge([
        'laboratorio_id' => $laboratorio->id,
        'tipo_avaliacao_id' => $tipo->id,
        'data_inicio' => now()->toDateString(),
        'data_fim' => now()->addDays(3)->toDateString(),
        'data_envio_proposta' => now()->toDateString(),
        'perc_lucro' => 15,
        'status_proposta' => 'PENDENTE',
    ], $atributos));
}

function criarAvaliadorOrcamento(): Avaliador
{
    $pessoa = PessoaFactory::new()->create([
        'tipo_pessoa' => 'PF',
        'cpf_cnpj' => fake()->unique()->numerify('###########'),
    ]);

    return Avaliador::query()->create([
        'pessoa_id' => $pessoa->id,
        'situacao' => 'AVALIADOR',
    ]);
}

function criarAreaAtuacaoOrcamento(string $descricao): AreaAtuacao
{
    return AreaAtuacao::query()->create([
        'descricao' => $descricao,
        'observacoes' => '',
    ]);
}

function criarAreaAvaliadaOrcamento(
    AgendaAvaliacao $avaliacao,
    AreaAtuacao $area,
    Avaliador $avaliador,
    array $atributos = []
): AreaAvaliada {
    return AreaAvaliada::query()->create(array_merge([
        'avaliacao_id' => $avaliacao->id,
        'area_atuacao_id' => $area->id,
        'avaliador_id' => $avaliador->id,
        'situacao' => 'AVALIADOR',
        'num_ensaios' => 1,
        'dias' => 1,
        'valor_avaliador' => 1000,
        'total_gastos_estim' => 200,
        'total_gastos_reais' => 150,
    ], $atributos));
}

test('monta o mini relatório com agregados das áreas', function () {
    $avaliacao = criarAgendaAvaliacaoOrcamento();
    $avaliadorA = criarAvaliadorOrcamento();
    $avaliadorB = criarAvaliadorOrcamento();
    $dimensional = criarAreaAtuacaoOrcamento('DIMENSIONAL');
    $forca = criarAreaAtuacaoOrcamento('FORÇA');

    criarAreaAvaliadaOrcamento($avaliacao, $dimensional, $avaliadorA, [
        'situacao' => 'AVALIADOR',
        'num_ensaios' => 2,
        'dias' => 1.5,
        'valor_avaliador' => 1000,
        'total_gastos_estim' => 200,
        'total_gastos_reais' => 100,
    ]);
    criarAreaAvaliadaOrcamento($avaliacao, $forca, $avaliadorA, [
        'situacao' => 'AVALIADOR EM TREINAMENTO',
        'num_ensaios' => 1,
        'dias' => 2,
        'valor_avaliador' => 500,
        'total_gastos_estim' => 100,
        'total_gastos_reais' => 50,
    ]);
    criarAreaAvaliadaOrcamento($avaliacao, $dimensional, $avaliadorB, [
        'situacao' => 'AVALIADOR EM TREINAMENTO',
        'num_ensaios' => 3,
        'dias' => 1,
        'valor_avaliador' => 300,
        'total_gastos_estim' => 50,
        'total_gastos_reais' => 25,
    ]);

    // valor_proposta = 1800 + (1800 * 0.15) + 350 = 2420
    Livewire::test(AgendaAvaliacoesOrcamentos::class, ['avaliacao' => $avaliacao->fresh()])
        ->assertSet('numAvaliadores', 2)
        ->assertSet('totalDiasTrabalho', 4.5)
        ->assertSet('numAvalTreinamento', 2)
        ->assertSet('avaliacoes', 'DIMENSIONAL, FORÇA, DIMENSIONAL')
        ->assertSet('numEnsaios', 6)
        ->assertSet('somaAvaliadores', 1800.0)
        ->assertSet('somaDespesasEstimadas', 350.0)
        ->assertSet('somaDespesasReais', 175.0)
        ->assertSet('valorProposta', 2420.0)
        ->assertSet('superavit', 445.0)
        ->assertSee('DIMENSIONAL, FORÇA, DIMENSIONAL')
        ->assertSee('Perc Lucro (%)')
        ->assertSee('Data Envio Proposta')
        ->assertDontSee('Num Aval Treinamento');
});

test('ao alterar perc lucro no blur recalcula e persiste valor e superavit', function () {
    $avaliacao = criarAgendaAvaliacaoOrcamento(['perc_lucro' => 10, 'valor_proposta' => 100]);
    $area = criarAreaAtuacaoOrcamento('TORQUE');
    $avaliador = criarAvaliadorOrcamento();

    criarAreaAvaliadaOrcamento($avaliacao, $area, $avaliador, [
        'valor_avaliador' => 1000,
        'total_gastos_estim' => 200,
        'total_gastos_reais' => 100,
    ]);

    Livewire::test(AgendaAvaliacoesOrcamentos::class, ['avaliacao' => $avaliacao->fresh()])
        ->set('form.perc_lucro', 20)
        ->assertSet('valorProposta', 1400.0)
        ->assertSet('superavit', 300.0);

    $avaliacao->refresh();

    expect((float) $avaliacao->perc_lucro)->toBe(20.0)
        ->and((float) $avaliacao->valor_proposta)->toBe(1400.0)
        ->and((float) $avaliacao->superavit)->toBe(300.0);
});

test('bloqueia gerar orçamento quando faltam dados principais', function (string $campo) {
    $atributos = match ($campo) {
        'data_inicio' => ['data_inicio' => null],
        'data_fim' => ['data_fim' => null],
        'tipo_avaliacao_id' => ['tipo_avaliacao_id' => null],
    };

    $avaliacao = criarAgendaAvaliacaoOrcamento($atributos);
    $avaliacao->update([
        'observacoes_orcamento' => 'antes',
        'valor_proposta' => 999,
    ]);

    Livewire::test(AgendaAvaliacoesOrcamentos::class, ['avaliacao' => $avaliacao->fresh()])
        ->set('form.observacoes_orcamento', 'depois')
        ->set('form.data_envio_proposta', now()->toDateString())
        ->call('gerarOrcamento')
        ->assertDispatched(
            'show-orcamento-validation-alert',
            message: AgendaAvaliacoesOrcamentos::MENSAGEM_CAMPOS_OBRIGATORIOS
        );

    $avaliacao->refresh();

    expect($avaliacao->observacoes_orcamento)->toBe('antes')
        ->and((float) $avaliacao->valor_proposta)->toBe(999.0);
})->with(['data_inicio', 'data_fim', 'tipo_avaliacao_id']);

test('com dados principais válidos persiste orçamento ao gerar', function () {
    $avaliacao = criarAgendaAvaliacaoOrcamento([
        'observacoes_orcamento' => 'antiga',
        'valor_proposta' => 1,
    ]);
    $area = criarAreaAtuacaoOrcamento('DUREZA');
    $avaliador = criarAvaliadorOrcamento();

    criarAreaAvaliadaOrcamento($avaliacao, $area, $avaliador, [
        'situacao' => 'AVALIADOR EM TREINAMENTO',
        'num_ensaios' => 4,
        'dias' => 2,
        'valor_avaliador' => 1000,
        'total_gastos_estim' => 200,
        'total_gastos_reais' => 100,
    ]);

    Livewire::test(AgendaAvaliacoesOrcamentos::class, ['avaliacao' => $avaliacao->fresh()])
        ->set('form.perc_lucro', 15)
        ->set('form.data_envio_proposta', now()->addDay()->toDateString())
        ->set('form.observacoes_orcamento', 'nova observação')
        ->call('gerarOrcamento')
        ->assertNotDispatched('show-orcamento-validation-alert');

    $avaliacao->refresh();

    expect((float) $avaliacao->num_ensaios)->toBe(4.0)
        ->and((float) $avaliacao->soma_avaliadores)->toBe(1000.0)
        ->and((float) $avaliacao->soma_despesas_estimadas)->toBe(200.0)
        ->and((float) $avaliacao->soma_despesas_reais)->toBe(100.0)
        ->and((float) $avaliacao->perc_lucro)->toBe(15.0)
        ->and((float) $avaliacao->valor_proposta)->toBe(1350.0)
        ->and((float) $avaliacao->superavit)->toBe(250.0)
        ->and((int) $avaliacao->num_aval_treinamento)->toBe(1)
        ->and($avaliacao->observacoes_orcamento)->toBe('nova observação');
});

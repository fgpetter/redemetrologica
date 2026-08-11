<?php

use App\Actions\Avaliacoes\CalcularOrcamentoAvaliacaoAction;
use App\Enums\ImpostoAvaliacao;
use App\Models\AgendaAvaliacao;
use App\Models\AreaAtuacao;
use App\Models\AreaAvaliada;
use App\Models\Avaliador;
use App\Models\Laboratorio;
use App\Models\TipoAvaliacao;
use Database\Factories\PessoaFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function criarAvaliacaoParaCalculo(array $atributos = []): AgendaAvaliacao
{
    $pessoa = PessoaFactory::new()->create();
    $laboratorio = Laboratorio::query()->create([
        'pessoa_id' => $pessoa->id,
        'nome_laboratorio' => 'Lab Cálculo',
        'contato' => 'Contato',
        'telefone' => '11999999999',
        'email' => 'lab-calc@example.com',
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
        'perc_lucro' => 15,
    ], $atributos));
}

test('calcula nf com fator 0.053 e despesas reais fora da base', function () {
    expect(ImpostoAvaliacao::Padrao->fator())->toBe(0.053);

    $avaliacao = criarAvaliacaoParaCalculo(['perc_lucro' => 15]);
    $area = AreaAtuacao::query()->create(['descricao' => 'DIMENSIONAL', 'observacoes' => '']);
    $pessoa = PessoaFactory::new()->create([
        'tipo_pessoa' => 'PF',
        'cpf_cnpj' => fake()->unique()->numerify('###########'),
        'nome_razao' => 'Avaliador A',
    ]);
    $avaliador = Avaliador::query()->create([
        'pessoa_id' => $pessoa->id,
        'situacao' => 'AVALIADOR',
    ]);

    AreaAvaliada::query()->create([
        'avaliacao_id' => $avaliacao->id,
        'area_atuacao_id' => $area->id,
        'avaliador_id' => $avaliador->id,
        'situacao' => 'AVALIADOR',
        'num_ensaios' => 2,
        'dias' => 1.5,
        'valor_avaliador' => 1800,
        'total_gastos_estim' => 350,
        'total_gastos_reais' => 175,
    ]);

    $totais = app(CalcularOrcamentoAvaliacaoAction::class)->execute($avaliacao->fresh());

    expect($totais['nf'])->toBe(113.95)
        ->and($totais['valor_proposta'])->toBe(2533.95)
        ->and($totais['superavit'])->toBe(445.0)
        ->and($totais['soma_avaliadores'])->toBe(1800.0)
        ->and($totais['num_ensaios'])->toBe(2.0)
        ->and($totais['total_geral_avaliadores'])->toBe(1800.0)
        ->and($totais['total_avaliadores']->first()['nome'])->toBe('Avaliador A');
});

test('aceita perc lucro opcional ainda não persistido', function () {
    $avaliacao = criarAvaliacaoParaCalculo(['perc_lucro' => 10]);
    $area = AreaAtuacao::query()->create(['descricao' => 'FORÇA', 'observacoes' => '']);
    $pessoa = PessoaFactory::new()->create([
        'tipo_pessoa' => 'PF',
        'cpf_cnpj' => fake()->unique()->numerify('###########'),
    ]);
    $avaliador = Avaliador::query()->create([
        'pessoa_id' => $pessoa->id,
        'situacao' => 'AVALIADOR',
    ]);

    AreaAvaliada::query()->create([
        'avaliacao_id' => $avaliacao->id,
        'area_atuacao_id' => $area->id,
        'avaliador_id' => $avaliador->id,
        'situacao' => 'AVALIADOR',
        'valor_avaliador' => 1000,
        'total_gastos_estim' => 200,
        'total_gastos_reais' => 100,
    ]);

    $totais = app(CalcularOrcamentoAvaliacaoAction::class)->execute($avaliacao->fresh(), 20.0);

    expect($totais['perc_lucro'])->toBe(20.0)
        ->and($totais['nf'])->toBe(63.6)
        ->and($totais['valor_proposta'])->toBe(1463.6)
        ->and($totais['superavit'])->toBe(300.0);
});

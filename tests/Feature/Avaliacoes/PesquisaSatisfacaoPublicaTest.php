<?php

use App\Models\AgendaAvaliacao;
use App\Models\AreaAtuacao;
use App\Models\AreaAvaliada;
use App\Models\Avaliador;
use App\Models\Laboratorio;
use Database\Factories\PessoaFactory;

function criarAvaliacaoPublicaPesquisa(array $atributos = []): AgendaAvaliacao
{
    $pessoa = PessoaFactory::new()->create([
        'nome_razao' => 'Laboratorio Publico Ltda',
        'tipo_pessoa' => 'PJ',
        'cpf_cnpj' => '12345678000199',
    ]);

    $laboratorio = Laboratorio::query()->create([
        'pessoa_id' => $pessoa->id,
        'nome_laboratorio' => 'Laboratorio Publico',
        'contato' => 'Contato',
        'telefone' => '11999999999',
        'email' => 'lab-publico@example.com',
        'responsavel_tecnico' => 'Responsável',
    ]);

    return AgendaAvaliacao::query()->create(array_merge([
        'laboratorio_id' => $laboratorio->id,
        'data_inicio' => '2026-01-10',
        'data_fim' => '2026-01-12',
        'carta_reconhecimento' => 1,
    ], $atributos));
}

function criarAreaPublicaPesquisa(AgendaAvaliacao $avaliacao): Avaliador
{
    $area = AreaAtuacao::query()->create(['descricao' => 'PRESSÃO', 'observacoes' => '']);
    $pessoa = PessoaFactory::new()->create([
        'tipo_pessoa' => 'PF',
        'cpf_cnpj' => fake()->unique()->numerify('###########'),
        'nome_razao' => 'Avaliador Publico',
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
    ]);

    return $avaliador;
}

function payloadPublicoPesquisa(Avaliador $avaliador, array $overrides = []): array
{
    return array_merge([
        'nota_1' => 7,
        'nota_2' => 10,
        'nota_3' => 10,
        'nota_4' => 7,
        'nota_5' => 10,
        'nota_6' => 9,
        'nota_7' => 9,
        'criterios_harmoniosos' => 'Sim',
        'divergencias' => 'Não',
        'pontos_melhoria' => 'Nenhum',
        'comentarios' => [$avaliador->id => 'Bom trabalho'],
        'responsavel' => 'João Silva',
    ], $overrides);
}

test('get indisponivel quando nao ha pesquisa', function () {
    $avaliacao = criarAvaliacaoPublicaPesquisa(['carta_reconhecimento' => 0]);

    $this->get(route('pesquisa-satisfacao.show', $avaliacao))
        ->assertSuccessful()
        ->assertSee('Esta pesquisa de satisfação não está disponível.');
});

test('post valido grava media 8.86', function () {
    $avaliacao = criarAvaliacaoPublicaPesquisa();
    $avaliacao->pesquisa()->create([]);
    $avaliador = criarAreaPublicaPesquisa($avaliacao);

    $this->post(route('pesquisa-satisfacao.submit', $avaliacao), payloadPublicoPesquisa($avaliador))
        ->assertSessionHas('success', 'Pesquisa de satisfação enviada. Obrigado.');

    $avaliacao->refresh();
    expect((float) $avaliacao->med_pesquisa)->toBe(8.86);
    expect($avaliacao->pesquisa->preenchido_em)->not->toBeNull();
    expect($avaliacao->pesquisa->responsavel)->toBe('João Silva');
});

test('get ja respondida nao exibe dados', function () {
    $avaliacao = criarAvaliacaoPublicaPesquisa();
    $avaliacao->pesquisa()->create([
        'preenchido_em' => now(),
        'responsavel' => 'Segredo',
    ]);

    $this->get(route('pesquisa-satisfacao.show', $avaliacao))
        ->assertSuccessful()
        ->assertSee('Esta pesquisa de satisfação já foi respondida. Obrigado.')
        ->assertDontSee('Enviar Pesquisa')
        ->assertDontSee('Segredo');
});

test('post incompleto nao grava', function () {
    $avaliacao = criarAvaliacaoPublicaPesquisa();
    $avaliacao->pesquisa()->create([]);
    $avaliador = criarAreaPublicaPesquisa($avaliacao);

    $this->from(route('pesquisa-satisfacao.show', $avaliacao))
        ->post(route('pesquisa-satisfacao.submit', $avaliacao), payloadPublicoPesquisa($avaliador, [
            'nota_1' => null,
            'responsavel' => '',
        ]))
        ->assertSessionHasErrors(['nota_1', 'responsavel']);

    $avaliacao->refresh();
    expect($avaliacao->pesquisa->preenchido_em)->toBeNull();
    expect($avaliacao->med_pesquisa)->toBeNull();
});

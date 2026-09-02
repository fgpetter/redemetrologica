<?php

use App\Mail\PesquisaSatisfacaoMail;
use App\Models\AgendaAvaliacao;
use App\Models\AreaAtuacao;
use App\Models\AreaAvaliada;
use App\Models\Avaliador;
use App\Models\Laboratorio;
use App\Models\Permission;
use App\Models\User;
use Database\Factories\PessoaFactory;
use Illuminate\Support\Facades\Mail;

function usuarioEnvioPesquisaSatisfacao(): User
{
    $user = User::factory()->create();
    $permission = Permission::withoutEvents(function (): Permission {
        return Permission::query()->firstOrCreate(['permission' => 'funcionario']);
    });
    $user->permissions()->syncWithoutDetaching([$permission->id]);

    return $user;
}

function criarAvaliacaoEnvioPesquisa(array $atributos = []): AgendaAvaliacao
{
    $pessoa = PessoaFactory::new()->create([
        'nome_razao' => 'Laboratorio Pesquisa Ltda',
        'tipo_pessoa' => 'PJ',
    ]);

    $laboratorio = Laboratorio::query()->create([
        'pessoa_id' => $pessoa->id,
        'nome_laboratorio' => 'Laboratorio Pesquisa',
        'contato' => 'Contato',
        'telefone' => '11999999999',
        'email' => 'lab-pesquisa@example.com',
        'responsavel_tecnico' => 'Responsável',
    ]);

    return AgendaAvaliacao::query()->create(array_merge([
        'laboratorio_id' => $laboratorio->id,
        'data_inicio' => now()->toDateString(),
        'perc_lucro' => 15,
        'carta_reconhecimento' => 0,
    ], $atributos));
}

function criarAreaComValorEnvioPesquisa(AgendaAvaliacao $avaliacao): void
{
    $area = AreaAtuacao::query()->create(['descricao' => 'DIMENSIONAL', 'observacoes' => '']);
    $pessoa = PessoaFactory::new()->create([
        'tipo_pessoa' => 'PF',
        'cpf_cnpj' => fake()->unique()->numerify('###########'),
        'nome_razao' => 'Avaliador Pesquisa',
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
}

test('transicao carta sim cria pesquisa e enfileira email', function () {
    Mail::fake();

    $avaliacao = criarAvaliacaoEnvioPesquisa();
    criarAreaComValorEnvioPesquisa($avaliacao);

    $response = $this->actingAs(usuarioEnvioPesquisaSatisfacao())->post(route('avaliacao-update', $avaliacao->uid), [
        'carta_reconhecimento' => '1',
    ]);

    $response->assertSessionHas('success');

    $avaliacao->refresh();
    expect($avaliacao->pesquisa)->not->toBeNull();
    expect($avaliacao->pesq_satisfacao)->not->toBeNull();
    expect(\Illuminate\Support\Carbon::parse($avaliacao->pesq_satisfacao)->toDateString())->toBe(now()->toDateString());

    Mail::assertQueued(PesquisaSatisfacaoMail::class, function ($mail) {
        return $mail->hasTo('lab-pesquisa@example.com');
    });
});

test('segundo save nao reenvia pesquisa', function () {
    Mail::fake();

    $avaliacao = criarAvaliacaoEnvioPesquisa();
    criarAreaComValorEnvioPesquisa($avaliacao);
    $user = usuarioEnvioPesquisaSatisfacao();

    $this->actingAs($user)->post(route('avaliacao-update', $avaliacao->uid), [
        'carta_reconhecimento' => '1',
    ]);

    $this->actingAs($user)->post(route('avaliacao-update', $avaliacao->uid), [
        'carta_reconhecimento' => '1',
    ]);

    expect($avaliacao->pesquisa()->count())->toBe(1);
    Mail::assertQueued(PesquisaSatisfacaoMail::class, 1);
});

test('laboratorio sem email gera pesquisa sem enfileirar e avisa', function () {
    Mail::fake();

    $avaliacao = criarAvaliacaoEnvioPesquisa();
    $avaliacao->laboratorio->update(['email' => '']);
    criarAreaComValorEnvioPesquisa($avaliacao);

    $response = $this->actingAs(usuarioEnvioPesquisaSatisfacao())->post(route('avaliacao-update', $avaliacao->uid), [
        'carta_reconhecimento' => '1',
    ]);

    $response->assertSessionHas('warning', 'Pesquisa gerada, mas o laboratório não tem e-mail cadastrado. Copie o link na aba Pesquisa de Avaliação.');

    $avaliacao->refresh();
    expect($avaliacao->pesquisa)->not->toBeNull();
    Mail::assertNotQueued(PesquisaSatisfacaoMail::class);
});

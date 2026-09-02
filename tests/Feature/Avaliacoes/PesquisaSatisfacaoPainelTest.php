<?php

use App\Models\AgendaAvaliacao;
use App\Models\AreaAtuacao;
use App\Models\AreaAvaliada;
use App\Models\Avaliador;
use App\Models\Laboratorio;
use App\Models\Permission;
use App\Models\User;
use Database\Factories\PessoaFactory;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\FakePdfBuilder;

function usuarioPainelPesquisaSatisfacao(): User
{
    $user = User::factory()->create();
    $permission = Permission::withoutEvents(function (): Permission {
        return Permission::query()->firstOrCreate(['permission' => 'funcionario']);
    });
    $user->permissions()->syncWithoutDetaching([$permission->id]);

    return $user;
}

function criarAvaliacaoPainelPesquisa(): AgendaAvaliacao
{
    $pessoa = PessoaFactory::new()->create([
        'nome_razao' => 'Laboratorio Painel Ltda',
        'tipo_pessoa' => 'PJ',
    ]);

    $laboratorio = Laboratorio::query()->create([
        'pessoa_id' => $pessoa->id,
        'nome_laboratorio' => 'Laboratorio Painel',
        'contato' => 'Contato',
        'telefone' => '11999999999',
        'email' => 'lab-painel@example.com',
        'responsavel_tecnico' => 'Responsável',
    ]);

    $avaliacao = AgendaAvaliacao::query()->create([
        'laboratorio_id' => $laboratorio->id,
        'data_inicio' => now()->toDateString(),
        'carta_reconhecimento' => 1,
        'med_pesquisa' => 8.86,
    ]);

    $area = AreaAtuacao::query()->create(['descricao' => 'TEMPERATURA', 'observacoes' => '']);
    $pessoaAvaliador = PessoaFactory::new()->create([
        'tipo_pessoa' => 'PF',
        'cpf_cnpj' => fake()->unique()->numerify('###########'),
        'nome_razao' => 'Avaliador Painel',
    ]);
    $avaliador = Avaliador::query()->create([
        'pessoa_id' => $pessoaAvaliador->id,
        'situacao' => 'AVALIADOR',
    ]);

    AreaAvaliada::query()->create([
        'avaliacao_id' => $avaliacao->id,
        'area_atuacao_id' => $area->id,
        'avaliador_id' => $avaliador->id,
        'situacao' => 'AVALIADOR',
    ]);

    $avaliacao->pesquisa()->create([
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
        'comentarios_avaliadores' => [[
            'avaliador_id' => $avaliador->id,
            'nome' => 'Avaliador Painel',
            'comentario' => 'Ok',
        ]],
        'responsavel' => 'João',
        'preenchido_em' => now(),
        'conferida' => false,
    ]);

    return $avaliacao;
}

test('alterar nota no painel recalcula media', function () {
    $avaliacao = criarAvaliacaoPainelPesquisa();
    $avaliadorId = $avaliacao->areas()->first()->avaliador_id;

    $this->actingAs(usuarioPainelPesquisaSatisfacao())->post(route('avaliacao-pesquisa-update', $avaliacao), [
        'nota_1' => 10,
        'nota_2' => 10,
        'nota_3' => 10,
        'nota_4' => 7,
        'nota_5' => 10,
        'nota_6' => 9,
        'nota_7' => 9,
        'criterios_harmoniosos' => 'Sim',
        'divergencias' => 'Não',
        'pontos_melhoria' => 'Nenhum',
        'comentarios' => [$avaliadorId => 'Atualizado'],
        'responsavel' => 'Maria',
        'conferida' => 0,
    ])->assertSessionHas('success');

    $avaliacao->refresh();
    expect((float) $avaliacao->med_pesquisa)->toBe(9.29);
});

test('conferida persiste no painel', function () {
    $avaliacao = criarAvaliacaoPainelPesquisa();
    $avaliadorId = $avaliacao->areas()->first()->avaliador_id;

    $this->actingAs(usuarioPainelPesquisaSatisfacao())->post(route('avaliacao-pesquisa-update', $avaliacao), [
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
        'comentarios' => [$avaliadorId => 'Ok'],
        'responsavel' => 'João',
        'conferida' => 1,
    ])->assertSessionHas('success');

    $avaliacao->refresh();
    expect($avaliacao->pesquisa->conferida)->toBeTrue();
    expect((float) $avaliacao->med_pesquisa)->toBe(8.86);
});

test('imprimir gera pdf com respostas da pesquisa', function () {
    $fake = new class extends FakePdfBuilder
    {
        public function save(string $path): self
        {
            parent::save($path);

            if (! is_dir(dirname($path))) {
                mkdir(dirname($path), 0777, true);
            }

            file_put_contents($path, '%PDF-1.4');

            return $this;
        }
    };

    Pdf::swap($fake);

    $avaliacao = criarAvaliacaoPainelPesquisa();

    $this->actingAs(usuarioPainelPesquisaSatisfacao())
        ->get(route('avaliacao-pesquisa-pdf', $avaliacao))
        ->assertDownload('pesquisa-satisfacao.pdf');

    Pdf::assertViewIs('pdf.avaliacoes.pesquisa');
    Pdf::assertSee('Laboratorio Painel');
    Pdf::assertSee('7');
    Pdf::assertSee('Sim');
    Pdf::assertSee('João');
});

test('imprimir pdf exige autenticacao', function () {
    $avaliacao = criarAvaliacaoPainelPesquisa();

    $this->get(route('avaliacao-pesquisa-pdf', $avaliacao))
        ->assertRedirect();
});

test('imprimir pdf sem pesquisa retorna 404', function () {
    $avaliacao = criarAvaliacaoPainelPesquisa();
    $avaliacao->pesquisa()->delete();

    $this->actingAs(usuarioPainelPesquisaSatisfacao())
        ->get(route('avaliacao-pesquisa-pdf', $avaliacao))
        ->assertNotFound();
});

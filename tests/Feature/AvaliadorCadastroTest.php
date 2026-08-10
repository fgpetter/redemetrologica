<?php

namespace Tests\Feature;

use App\Models\Avaliador;
use App\Models\Permission;
use App\Models\User;
use Database\Factories\PessoaFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvaliadorCadastroTest extends TestCase
{
    use RefreshDatabase;

    private function usuarioFuncionario(): User
    {
        $user = User::factory()->create();

        $permission = Permission::withoutEvents(function (): Permission {
            return Permission::query()->firstOrCreate(['permission' => 'funcionario']);
        });

        $user->permissions()->syncWithoutDetaching([$permission->id]);

        return $user;
    }

    public function test_listagem_nao_exibe_mais_busca_de_pessoas(): void
    {
        $response = $this->actingAs($this->usuarioFuncionario())->get(route('avaliador-index'));

        $response->assertOk();
        $response->assertSee(route('avaliador-insert'), false);
        $response->assertDontSee('id="tom-select"', false);
        $response->assertDontSee('Caso a pessoa não esteja cadastrada ainda', false);
    }

    public function test_tela_de_insercao_sem_uid_exibe_apenas_aba_dados_principais(): void
    {
        $pessoa = PessoaFactory::new()->create(['tipo_pessoa' => 'PF']);

        $response = $this->actingAs($this->usuarioFuncionario())->get(route('avaliador-insert'));

        $response->assertOk();
        $response->assertSee('id="tom-select-avaliador-pessoa"', false);
        $response->assertSee($pessoa->uid, false);
        $response->assertDontSee('id="enderecos"', false);
        $response->assertDontSee('id="avaliacoes"', false);
        $response->assertDontSee('id="areasatuacao"', false);
        $response->assertDontSee('id="qualificacoes"', false);
        $response->assertDontSee('id="controlestatus"', false);
    }

    public function test_tela_de_insercao_nao_lista_pessoa_ja_vinculada_a_avaliador(): void
    {
        $pessoaLivre = PessoaFactory::new()->create(['tipo_pessoa' => 'PF']);
        $pessoaVinculada = PessoaFactory::new()->create(['tipo_pessoa' => 'PF']);
        Avaliador::create(['pessoa_id' => $pessoaVinculada->id]);

        $response = $this->actingAs($this->usuarioFuncionario())->get(route('avaliador-insert'));

        $response->assertSee($pessoaLivre->uid, false);
        $response->assertDontSee($pessoaVinculada->uid, false);
    }

    public function test_create_cadastra_avaliador_em_um_unico_submit_e_atualiza_pessoa(): void
    {
        $pessoa = PessoaFactory::new()->create([
            'tipo_pessoa' => 'PF',
            'cpf_cnpj' => '11122233344',
            'rg_ie' => '111111',
            'telefone' => '11999998888',
            'email' => 'antigo@example.com',
        ]);

        $this->assertDatabaseCount('avaliadores', 0);

        $response = $this->actingAs($this->usuarioFuncionario())->post(route('avaliador-create'), [
            'pessoa_uid' => $pessoa->uid,
            'situacao' => 'ATIVO',
            'cpf_cnpj' => '999.888.777-66',
            'rg_ie' => '222222',
            'telefone' => '11988887777',
            'email' => 'novo@example.com',
            'data_ingresso' => '2026-01-01',
            'exp_min_comprovada' => '1',
        ]);

        $avaliador = Avaliador::where('pessoa_id', $pessoa->id)->first();

        $this->assertNotNull($avaliador);
        $response->assertRedirect(route('avaliador-insert', $avaliador->uid));
        $response->assertSessionHas('success');
        $this->assertEquals('ATIVO', $avaliador->situacao);
        $this->assertEquals('2026-01-01', $avaliador->data_ingresso->format('Y-m-d'));
        $this->assertTrue((bool) $avaliador->exp_min_comprovada);

        $pessoa->refresh();
        $this->assertEquals('99988877766', $pessoa->getRawOriginal('cpf_cnpj'));
        $this->assertEquals('222222', $pessoa->rg_ie);
        $this->assertEquals('11988887777', $pessoa->getRawOriginal('telefone'));
        $this->assertEquals('novo@example.com', $pessoa->email);
    }

    public function test_create_exige_pessoa_e_situacao(): void
    {
        $response = $this->actingAs($this->usuarioFuncionario())->post(route('avaliador-create'), []);

        $response->assertSessionHasErrors(['pessoa_uid', 'cpf_cnpj', 'situacao']);
        $this->assertDatabaseCount('avaliadores', 0);
    }

    public function test_edicao_de_avaliador_existente_continua_exibindo_todas_as_abas(): void
    {
        $pessoa = PessoaFactory::new()->create(['tipo_pessoa' => 'PF']);
        $avaliador = Avaliador::create(['pessoa_id' => $pessoa->id]);

        $response = $this->actingAs($this->usuarioFuncionario())->get(route('avaliador-insert', $avaliador->uid));

        $response->assertOk();
        $response->assertSee('id="enderecos"', false);
        $response->assertDontSee('id="tom-select-avaliador-pessoa"', false);
    }
}

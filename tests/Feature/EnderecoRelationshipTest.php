<?php

namespace Tests\Feature;

use App\Models\Avaliador;
use App\Models\Endereco;
use App\Models\Pessoa;
use App\Models\Permission;
use App\Models\Unidade;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class EnderecoRelationshipTest extends TestCase
{
    use DatabaseTransactions;

    private function criarUsuarioAutenticado(): User
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test-endereco-' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
        ]);

        $permission = DB::table('permissions')->where('permission', 'admin')->first();
        if (! $permission) {
            $permissionId = DB::table('permissions')->insertGetId([
                'permission' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $permissionId = $permission->id;
        }

        $user->permissions()->attach($permissionId);

        return $user;
    }

    private function criarEndereco(array $attrs = []): Endereco
    {
        return Endereco::create(array_merge([
            'endereco' => 'Rua Teste, 123',
            'bairro' => 'Centro',
            'cidade' => 'Porto Alegre',
            'uf' => 'RS',
            'cep' => '90000000',
        ], $attrs));
    }

    // ---- Pessoa ----

    public function test_pessoa_tem_endereco_principal(): void
    {
        $endereco = $this->criarEndereco();
        $pessoa = Pessoa::create([
            'nome_razao' => 'Pessoa Teste',
            'tipo_pessoa' => 'PF',
            'cpf_cnpj' => '00000000000',
            'endereco_id' => $endereco->id,
        ]);

        $this->assertNotNull($pessoa->endereco);
        $this->assertEquals($endereco->id, $pessoa->endereco->id);
        $this->assertEquals('Porto Alegre', $pessoa->endereco->cidade);
    }

    public function test_pessoa_tem_endereco_cobranca(): void
    {
        $endereco = $this->criarEndereco();
        $enderecoCobranca = $this->criarEndereco(['endereco' => 'Rua Cobrança, 456', 'cidade' => 'Canoas']);
        $pessoa = Pessoa::create([
            'nome_razao' => 'Pessoa Teste',
            'tipo_pessoa' => 'PF',
            'cpf_cnpj' => '00000000001',
            'endereco_id' => $endereco->id,
            'endereco_cobranca_id' => $enderecoCobranca->id,
        ]);

        $this->assertNotNull($pessoa->enderecoCobranca);
        $this->assertEquals($enderecoCobranca->id, $pessoa->enderecoCobranca->id);
        $this->assertEquals('Canoas', $pessoa->enderecoCobranca->cidade);
    }

    public function test_pessoa_insert_exibe_endereco_na_tela(): void
    {
        $user = $this->criarUsuarioAutenticado();
        $endereco = $this->criarEndereco(['endereco' => 'Av. Ipiranga, 6681']);
        $pessoa = Pessoa::create([
            'nome_razao' => 'Pessoa Tela Teste',
            'tipo_pessoa' => 'PF',
            'cpf_cnpj' => '00000000002',
            'user_id' => $user->id,
            'endereco_id' => $endereco->id,
        ]);

        $response = $this->actingAs($user)
            ->get(route('pessoa-insert', $pessoa->uid));

        $response->assertStatus(200);
        $response->assertSeeLivewire(\App\Livewire\Enderecos\Listview::class);
    }

    public function test_livewire_listview_exibe_endereco_pessoa(): void
    {
        $endereco = $this->criarEndereco(['endereco' => 'Av. Ipiranga, 6681', 'cidade' => 'Porto Alegre']);
        $pessoa = Pessoa::create([
            'nome_razao' => 'Pessoa Livewire Teste',
            'tipo_pessoa' => 'PF',
            'cpf_cnpj' => '00000000003',
            'endereco_id' => $endereco->id,
        ]);

        Livewire::test(\App\Livewire\Enderecos\Listview::class, ['pessoa' => $pessoa])
            ->assertSee('Av. Ipiranga, 6681')
            ->assertSee('Porto Alegre')
            ->assertSee('Principal');
    }

    public function test_livewire_listview_exibe_endereco_cobranca(): void
    {
        $endereco = $this->criarEndereco(['endereco' => 'Rua Principal, 100']);
        $enderecoCobranca = $this->criarEndereco(['endereco' => 'Rua Cobrança, 200', 'cidade' => 'Gravataí']);
        $pessoa = Pessoa::create([
            'nome_razao' => 'Pessoa Cobrança Teste',
            'tipo_pessoa' => 'PF',
            'cpf_cnpj' => '00000000004',
            'endereco_id' => $endereco->id,
            'endereco_cobranca_id' => $enderecoCobranca->id,
        ]);

        Livewire::test(\App\Livewire\Enderecos\Listview::class, ['pessoa' => $pessoa])
            ->assertSee('Rua Principal, 100')
            ->assertSee('Rua Cobrança, 200')
            ->assertSee('Gravataí')
            ->assertSee('Principal')
            ->assertSee('Cobrança');
    }

    // ---- Unidade ----

    public function test_unidade_tem_endereco(): void
    {
        $endereco = $this->criarEndereco(['cidade' => 'Novo Hamburgo']);
        $pessoa = Pessoa::create([
            'nome_razao' => 'Empresa Teste',
            'tipo_pessoa' => 'PJ',
            'cpf_cnpj' => '00000000000100',
        ]);
        $unidade = Unidade::create([
            'nome' => 'Unidade Teste',
            'pessoa_id' => $pessoa->id,
            'endereco_id' => $endereco->id,
        ]);

        $this->assertNotNull($unidade->endereco);
        $this->assertEquals($endereco->id, $unidade->endereco->id);
        $this->assertEquals('Novo Hamburgo', $unidade->endereco->cidade);
    }

    public function test_pessoa_carrega_unidades_com_endereco(): void
    {
        $endereco = $this->criarEndereco(['cidade' => 'São Leopoldo']);
        $pessoa = Pessoa::create([
            'nome_razao' => 'Empresa Unidade',
            'tipo_pessoa' => 'PJ',
            'cpf_cnpj' => '00000000000200',
        ]);
        Unidade::create([
            'nome' => 'Unidade SL',
            'pessoa_id' => $pessoa->id,
            'endereco_id' => $endereco->id,
        ]);

        $pessoa->load('unidades');
        $unidade = $pessoa->unidades->first();

        $this->assertNotNull($unidade);
        $this->assertNotNull($unidade->endereco);
        $this->assertEquals('São Leopoldo', $unidade->endereco->cidade);
    }

    // ---- Avaliador ----

    public function test_avaliador_tem_endereco_pessoal(): void
    {
        $endereco = $this->criarEndereco(['cidade' => 'Caxias do Sul']);
        $pessoa = Pessoa::create([
            'nome_razao' => 'Avaliador Pessoa',
            'tipo_pessoa' => 'PF',
            'cpf_cnpj' => '00000000005',
        ]);
        $avaliador = Avaliador::create([
            'pessoa_id' => $pessoa->id,
            'endereco_pessoal_id' => $endereco->id,
        ]);

        $this->assertNotNull($avaliador->enderecoPessoal);
        $this->assertEquals($endereco->id, $avaliador->enderecoPessoal->id);
        $this->assertEquals('Caxias do Sul', $avaliador->enderecoPessoal->cidade);
    }

    public function test_avaliador_tem_endereco_comercial(): void
    {
        $enderecoComercial = $this->criarEndereco(['cidade' => 'Pelotas']);
        $pessoa = Pessoa::create([
            'nome_razao' => 'Avaliador Comercial',
            'tipo_pessoa' => 'PF',
            'cpf_cnpj' => '00000000006',
        ]);
        $avaliador = Avaliador::create([
            'pessoa_id' => $pessoa->id,
            'endereco_comercial_id' => $enderecoComercial->id,
        ]);

        $this->assertNotNull($avaliador->enderecoComercial);
        $this->assertEquals($enderecoComercial->id, $avaliador->enderecoComercial->id);
        $this->assertEquals('Pelotas', $avaliador->enderecoComercial->cidade);
    }

    public function test_avaliador_insert_exibe_enderecos_na_tela(): void
    {
        $user = $this->criarUsuarioAutenticado();
        $enderecoPessoal = $this->criarEndereco(['endereco' => 'Rua Pessoal, 10', 'cep' => '91000000']);
        $enderecoComercial = $this->criarEndereco(['endereco' => 'Rua Comercial, 20', 'cep' => '92000000']);
        $pessoa = Pessoa::create([
            'nome_razao' => 'Avaliador Tela',
            'tipo_pessoa' => 'PF',
            'cpf_cnpj' => '00000000007',
        ]);
        $avaliador = Avaliador::create([
            'pessoa_id' => $pessoa->id,
            'endereco_pessoal_id' => $enderecoPessoal->id,
            'endereco_comercial_id' => $enderecoComercial->id,
            'data_ingresso' => '2025-01-01',
        ]);

        $response = $this->actingAs($user)
            ->get(route('avaliador-insert', $avaliador->uid));

        $response->assertStatus(200);
        $response->assertSee('Rua Pessoal, 10');
        $response->assertSee('Rua Comercial, 20');
        $response->assertSee('91000-000');
        $response->assertSee('92000-000');
    }

    public function test_avaliador_sem_endereco_exibe_pagina_normalmente(): void
    {
        $user = $this->criarUsuarioAutenticado();
        $pessoa = Pessoa::create([
            'nome_razao' => 'Avaliador Sem End',
            'tipo_pessoa' => 'PF',
            'cpf_cnpj' => '00000000008',
        ]);
        $avaliador = Avaliador::create([
            'pessoa_id' => $pessoa->id,
            'data_ingresso' => '2025-01-01',
        ]);

        $response = $this->actingAs($user)
            ->get(route('avaliador-insert', $avaliador->uid));

        $response->assertStatus(200);
        $response->assertSee('Endereço Pessoal');
        $response->assertSee('Endereço Comercial');
    }

    public function test_pessoa_sem_endereco_nao_quebra_listview(): void
    {
        $pessoa = Pessoa::create([
            'nome_razao' => 'Pessoa Sem Endereco',
            'tipo_pessoa' => 'PF',
            'cpf_cnpj' => '00000000009',
        ]);

        Livewire::test(\App\Livewire\Enderecos\Listview::class, ['pessoa' => $pessoa])
            ->assertSee('Não há endereço cadastrado')
            ->assertDontSee('badge bg-primary');
    }
}

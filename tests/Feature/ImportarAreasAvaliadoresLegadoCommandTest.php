<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ImportarAreasAvaliadoresLegadoCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_simulacao_mostra_snapshot_e_nao_persiste_vinculos(): void
    {
        $this->prepararTabelasLegado();
        $this->prepararRegistroLegado();
        $this->prepararPessoaEAvaliador();
        DB::table('areas_atuacao')->insert(['uid' => 'area-teste', 'descricao' => 'Química', 'observacoes' => '', 'created_at' => now(), 'updated_at' => now()]);

        $this->artisan('avaliadores:importar-areas-legado')
            ->assertSuccessful()
            ->expectsOutputToContain('Snapshot:')
            ->expectsOutputToContain('A importar: 1');

        $this->assertDatabaseCount('avaliador_areas', 0);
    }

    public function test_apply_com_snapshot_importa_tuplas_distintas_e_preserva_status(): void
    {
        $this->prepararTabelasLegado();
        $this->prepararRegistroLegado();
        $this->prepararPessoaEAvaliador();
        DB::table('areas_atuacao')->insert(['uid' => 'area-teste', 'descricao' => 'Química', 'observacoes' => '', 'created_at' => now(), 'updated_at' => now()]);
        DB::connection('legado')->table('pessoas_areas_atuacao')->insert([
            ['id' => 2, 'id_pessoa' => 72, 'id_area_atuacao' => 3, 'data_cad_area' => '2021-02-03', 'id_situacao' => 8],
            ['id' => 3, 'id_pessoa' => 72, 'id_area_atuacao' => 3, 'data_cad_area' => '2022-04-05', 'id_situacao' => 10],
            ['id' => 4, 'id_pessoa' => 72, 'id_area_atuacao' => 3, 'data_cad_area' => '2021-02-03', 'id_situacao' => 8],
        ]);
        DB::table('areas_atuacao')->insert(['id' => 3, 'uid' => 'area-teste-3', 'descricao' => 'Microbiologia', 'observacoes' => '', 'created_at' => now(), 'updated_at' => now()]);
        DB::connection('legado')->table('areas_atuacao')->insert(['id' => 3, 'descricao' => 'Microbiologia']);

        Artisan::call('avaliadores:importar-areas-legado');
        preg_match('/Snapshot: ([a-f0-9]{64})/', Artisan::output(), $matches);

        $this->assertArrayHasKey(1, $matches);
        $this->artisan('avaliadores:importar-areas-legado', ['--apply' => true, '--snapshot' => $matches[1]])
            ->assertSuccessful()
            ->expectsOutputToContain('IDs inseridos:');

        $this->assertDatabaseCount('avaliador_areas', 3);
        foreach (DB::table('avaliador_areas')->pluck('uid') as $uid) {
            $this->assertMatchesRegularExpression('/^[a-f0-9]{13}$/', $uid);
        }
        $this->assertDatabaseHas('avaliador_areas', ['area_id' => 3, 'situacao' => 'AVALIADOR LIDER', 'data_cadastro' => '2021-02-03']);
        $this->assertDatabaseHas('avaliador_areas', ['area_id' => 3, 'situacao' => 'AVALIADOR', 'data_cadastro' => '2022-04-05']);
        $this->assertDatabaseHas('avaliador_areas', ['area_id' => 1, 'situacao' => 'AVALIADOR EM TREINAMENTO']);
    }

    public function test_apply_sem_snapshot_nao_grava_vinculo(): void
    {
        $this->prepararTabelasLegado();
        $this->prepararRegistroLegado();
        $this->prepararPessoaEAvaliador();
        DB::table('areas_atuacao')->insert(['uid' => 'area-teste', 'descricao' => 'Química', 'observacoes' => '', 'created_at' => now(), 'updated_at' => now()]);

        $this->artisan('avaliadores:importar-areas-legado', ['--apply' => true])
            ->assertFailed();

        $this->assertDatabaseCount('avaliador_areas', 0);
    }

    public function test_apply_aborta_se_destino_tem_tupla_extra(): void
    {
        $this->prepararTabelasLegado();
        $this->prepararRegistroLegado();
        $avaliadorId = $this->prepararPessoaEAvaliador();
        DB::table('areas_atuacao')->insert(['uid' => 'area-teste', 'descricao' => 'Química', 'observacoes' => '', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('avaliador_areas')->insert([
            'uid' => 'extra-destino', 'avaliador_id' => $avaliadorId, 'area_id' => 1, 'data_cadastro' => '2020-01-01',
            'situacao' => 'AVALIADOR', 'created_at' => now(), 'updated_at' => now(),
        ]);

        Artisan::call('avaliadores:importar-areas-legado');
        preg_match('/Snapshot: ([a-f0-9]{64})/', Artisan::output(), $matches);
        $this->assertArrayHasKey(1, $matches);

        $this->artisan('avaliadores:importar-areas-legado', ['--apply' => true, '--snapshot' => $matches[1]])
            ->assertFailed()
            ->expectsOutputToContain('vínculos no destino');

        $this->assertDatabaseCount('avaliador_areas', 1);
    }

    public function test_apply_rejeita_snapshot_desatualizado(): void
    {
        $this->prepararTabelasLegado();
        $this->prepararRegistroLegado();
        $this->prepararPessoaEAvaliador();
        DB::table('areas_atuacao')->insert(['uid' => 'area-teste', 'descricao' => 'Química', 'observacoes' => '', 'created_at' => now(), 'updated_at' => now()]);
        Artisan::call('avaliadores:importar-areas-legado');
        preg_match('/Snapshot: ([a-f0-9]{64})/', Artisan::output(), $matches);
        $this->assertArrayHasKey(1, $matches);
        DB::connection('legado')->table('pessoas_areas_atuacao')->update(['id_situacao' => 10]);

        $this->artisan('avaliadores:importar-areas-legado', ['--apply' => true, '--snapshot' => $matches[1]])
            ->assertFailed()
            ->expectsOutputToContain('Os dados mudaram desde a simulação');

        $this->assertDatabaseCount('avaliador_areas', 0);
    }

    private function prepararPessoaEAvaliador(): int
    {
        DB::table('pessoas')->insert([
            'uid' => 'pessoa-legado', 'nome_razao' => 'Pessoa Legado', 'cpf_cnpj' => '52998224725', 'tipo_pessoa' => 'PF',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $pessoaId = (int) DB::table('pessoas')->value('id');
        DB::table('avaliadores')->insert([
            'uid' => 'avaliador-legado', 'pessoa_id' => $pessoaId, 'created_at' => now(), 'updated_at' => now(),
        ]);

        return (int) DB::table('avaliadores')->value('id');
    }

    private function prepararRegistroLegado(): void
    {
        DB::connection('legado')->table('cad_pessoas')->insert(['id_pessoa' => 72, 'cpf_cnpj' => '529.982.247-25']);
        DB::connection('legado')->table('pessoas_tipo_pessoas')->insert(['id' => 1, 'id_pessoa' => 72, 'id_tipo_pessoa' => 6]);
        DB::connection('legado')->table('areas_atuacao')->insert(['id' => 1, 'descricao' => 'Química']);
        DB::connection('legado')->table('pessoas_areas_atuacao')->insert(['id' => 1, 'id_pessoa' => 72, 'id_area_atuacao' => 1, 'data_cad_area' => '2020-01-02', 'id_situacao' => 7]);
    }

    private function prepararTabelasLegado(): void
    {
        config(['database.connections.legado' => config('database.connections.'.config('database.default'))]);
        config(['database.connections.legado.database' => ':memory:']);
        config(['database.connections.legado.driver' => 'sqlite']);
        config(['database.connections.legado.url' => null]);
        config(['database.connections.legado.prefix' => '']);
        DB::purge('legado');

        Schema::connection('legado')->create('cad_pessoas', function ($table): void {
            $table->unsignedBigInteger('id_pessoa')->primary();
            $table->string('cpf_cnpj');
        });
        Schema::connection('legado')->create('pessoas_tipo_pessoas', function ($table): void {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('id_pessoa');
            $table->unsignedBigInteger('id_tipo_pessoa');
        });
        Schema::connection('legado')->create('areas_atuacao', function ($table): void {
            $table->unsignedBigInteger('id')->primary();
            $table->string('descricao');
        });
        Schema::connection('legado')->create('pessoas_areas_atuacao', function ($table): void {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('id_pessoa');
            $table->unsignedBigInteger('id_area_atuacao');
            $table->date('data_cad_area')->nullable();
            $table->integer('id_situacao')->nullable();
        });
    }
}

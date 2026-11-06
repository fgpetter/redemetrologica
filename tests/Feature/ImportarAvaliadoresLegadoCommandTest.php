<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ImportarAvaliadoresLegadoCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_comando_de_importacao_esta_registrado_no_artisan(): void
    {
        Artisan::call('list', ['--raw' => true]);

        $this->assertStringContainsString('avaliadores:importar-legado', Artisan::output());
    }

    public function test_simulacao_exibe_snapshot_sem_alterar_avaliadores(): void
    {
        $this->prepararTabelasLegado();
        DB::connection('legado')->table('cad_pessoas')->insert([
            'id_pessoa' => 72,
            'cpf_cnpj' => '529.982.247-25',
            'curriculo' => null,
            'situacao' => 1,
            'exp_min_comprovada' => 'SIM',
            'curso_incerteza' => 'NAO',
            'curso_iso' => null,
            'curso_aud_interna' => 'NÃO',
            'parecer_psicologico' => 'SIM',
            'data_ingresso' => '2020-03-04',
        ]);
        DB::connection('legado')->table('pessoas_tipo_pessoas')->insert([
            'id' => 1,
            'id_pessoa' => 72,
            'id_tipo_pessoa' => 6,
        ]);

        $this->artisan('avaliadores:importar-legado')
            ->assertSuccessful()
            ->expectsOutputToContain('Snapshot:');

        $this->assertDatabaseCount('avaliadores', 0);
        $this->assertDatabaseCount('pessoas', 0);
    }

    public function test_apply_sem_snapshot_nao_grava_dados(): void
    {
        $this->prepararTabelasLegado();

        $this->artisan('avaliadores:importar-legado', ['--apply' => true])
            ->assertFailed();

        $this->assertDatabaseCount('avaliadores', 0);
        $this->assertDatabaseCount('pessoas', 0);
    }

    public function test_apply_com_snapshot_importa_campos_para_pessoa_existente(): void
    {
        $this->prepararTabelasLegado();
        DB::table('pessoas')->insert([
            'uid' => 'pessoa-legado-teste',
            'nome_razao' => 'Pessoa Legado Teste',
            'cpf_cnpj' => '52998224725',
            'tipo_pessoa' => 'PF',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::connection('legado')->table('cad_pessoas')->insert([
            'id_pessoa' => 72,
            'cpf_cnpj' => '529.982.247-25',
            'curriculo' => null,
            'data_ingresso' => '2020-03-04',
            'situacao' => '1',
            'exp_min_comprovada' => 'SIM',
            'curso_incerteza' => 'NAO',
            'curso_iso' => null,
            'curso_aud_interna' => 'NÃO',
            'parecer_psicologico' => 'SIM',
        ]);
        DB::connection('legado')->table('pessoas_tipo_pessoas')->insert([
            'id' => 1,
            'id_pessoa' => 72,
            'id_tipo_pessoa' => 6,
        ]);

        Artisan::call('avaliadores:importar-legado');
        preg_match('/Snapshot: ([a-f0-9]{64})/', Artisan::output(), $matches);

        $this->assertArrayHasKey(1, $matches);
        $this->artisan('avaliadores:importar-legado', ['--apply' => true, '--snapshot' => $matches[1]])
            ->assertSuccessful();

        $this->assertDatabaseHas('avaliadores', [
            'pessoa_id' => DB::table('pessoas')->value('id'),
            'curriculo' => null,
            'situacao' => 'ATIVO',
            'exp_min_comprovada' => 1,
            'curso_incerteza' => 0,
            'curso_iso' => 0,
            'curso_aud_interna' => 0,
            'parecer_psicologico' => 1,
            'data_ingresso' => '2020-03-04',
        ]);
    }

    public function test_simulacao_revisa_vinculo_orfao_pessoa_excluida_e_cpf_duplicado_sem_incluir_nao_avaliador(): void
    {
        $this->prepararTabelasLegado();
        DB::connection('legado')->table('pessoas_tipo_pessoas')->insert([
            ['id' => 1, 'id_pessoa' => 800, 'id_tipo_pessoa' => 6],
            ['id' => 2, 'id_pessoa' => 801, 'id_tipo_pessoa' => 6],
            ['id' => 3, 'id_pessoa' => 802, 'id_tipo_pessoa' => 6],
            ['id' => 4, 'id_pessoa' => 803, 'id_tipo_pessoa' => 6],
            ['id' => 5, 'id_pessoa' => 804, 'id_tipo_pessoa' => 8],
        ]);
        DB::connection('legado')->table('cad_pessoas')->insert([
            $this->avaliadorLegado(801, '529.982.247-25'),
            $this->avaliadorLegado(802, '52998224725'),
            $this->avaliadorLegado(803, '52998224725'),
            $this->avaliadorLegado(804, '52998224725'),
        ]);
        DB::table('pessoas')->insert([
            'uid' => 'pessoa-excluida-legado',
            'nome_razao' => 'Pessoa Excluída',
            'cpf_cnpj' => '11144477735',
            'tipo_pessoa' => 'PF',
            'deleted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::connection('legado')->table('cad_pessoas')->where('id_pessoa', 801)->update(['cpf_cnpj' => '11144477735']);
        DB::connection('legado')->table('cad_pessoas')->where('id_pessoa', 802)->update(['cpf_cnpj' => '529.982.247-25']);

        Artisan::call('avaliadores:importar-legado');
        $output = Artisan::output();

        $this->assertStringContainsString('Cadastro legado ausente para o vínculo', $output);
        $this->assertStringContainsString('Pessoa correspondente excluída', $output);
        $this->assertStringContainsString('CPF duplicado na origem', $output);
        $this->assertStringNotContainsString('804', $output);
        $this->assertStringNotContainsString('52998224725', $output);
        $this->assertDatabaseCount('avaliadores', 0);
    }

    /** @return array<string, mixed> */
    private function avaliadorLegado(int $id, string $cpf): array
    {
        return [
            'id_pessoa' => $id,
            'cpf_cnpj' => $cpf,
            'curriculo' => 'Não importar',
            'data_ingresso' => null,
            'situacao' => '1',
            'exp_min_comprovada' => 'SIM',
            'curso_incerteza' => 'NAO',
            'curso_iso' => null,
            'curso_aud_interna' => 'NAO',
            'parecer_psicologico' => 'NAO',
        ];
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
            $table->text('curriculo')->nullable();
            $table->date('data_ingresso')->nullable();
            $table->string('situacao')->nullable();
            $table->string('exp_min_comprovada')->nullable();
            $table->string('curso_incerteza')->nullable();
            $table->string('curso_iso')->nullable();
            $table->string('curso_aud_interna')->nullable();
            $table->string('parecer_psicologico')->nullable();
        });

        Schema::connection('legado')->create('pessoas_tipo_pessoas', function ($table): void {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('id_pessoa');
            $table->unsignedBigInteger('id_tipo_pessoa');
        });
    }
}

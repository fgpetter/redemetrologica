<?php

use App\Models\AgendaAvaliacao;
use App\Models\AreaAtuacao;
use App\Models\AreaAvaliada;
use App\Models\Avaliador;
use App\Models\Laboratorio;
use App\Models\Permission;
use App\Models\TipoAvaliacao;
use App\Models\User;
use Database\Factories\PessoaFactory;

function usuarioFuncionarioAvaliacao(): User
{
    $user = User::factory()->create();
    $permission = Permission::withoutEvents(function (): Permission {
        return Permission::query()->firstOrCreate(['permission' => 'funcionario']);
    });
    $user->permissions()->syncWithoutDetaching([$permission->id]);

    return $user;
}

function criarAgendaParaAreas(): AgendaAvaliacao
{
    $pessoa = PessoaFactory::new()->create();
    $laboratorio = Laboratorio::query()->create([
        'pessoa_id' => $pessoa->id,
        'nome_laboratorio' => 'Lab Áreas',
        'contato' => 'Contato',
        'telefone' => '11999999999',
        'email' => 'areas@example.com',
        'responsavel_tecnico' => 'Responsável',
    ]);
    $tipo = TipoAvaliacao::query()->create(['descricao' => 'Inicial']);

    return AgendaAvaliacao::query()->create([
        'laboratorio_id' => $laboratorio->id,
        'tipo_avaliacao_id' => $tipo->id,
        'data_inicio' => now()->toDateString(),
        'data_fim' => now()->addDays(2)->toDateString(),
        'perc_lucro' => 15,
    ]);
}

function criarAvaliadorParaAreas(): Avaliador
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

test('cria área com valores derivados calculados', function () {
    $avaliacao = criarAgendaParaAreas();
    $area = AreaAtuacao::query()->create(['descricao' => 'PRESSÃO', 'observacoes' => '']);
    $avaliador = criarAvaliadorParaAreas();

    $response = $this->actingAs(usuarioFuncionarioAvaliacao())->post(route('avaliacao-save-area'), [
        'avaliacao_id' => $avaliacao->id,
        'area_atuacao_id' => $area->id,
        'avaliador_id' => $avaliador->id,
        'situacao' => 'AVALIADOR',
        'num_ensaios' => 2,
        'dias' => 1.5,
        'valor_dia' => '1.000,00',
        'valor_lider' => '100,00',
        'valor_estim_desloc' => '50,00',
        'valor_estim_alim' => '30,00',
        'valor_estim_hosped' => '20,00',
        'valor_estim_extras' => '10,00',
        'valor_real_desloc' => '40,00',
        'valor_real_alim' => '25,00',
        'valor_real_hosped' => '15,00',
        'valor_real_extras' => '5,00',
    ]);

    $response->assertRedirect();

    $areaSalva = AreaAvaliada::query()->where('avaliacao_id', $avaliacao->id)->first();

    expect($areaSalva)->not->toBeNull()
        ->and((float) $areaSalva->dias)->toBe(1.5)
        ->and((float) $areaSalva->valor_avaliador)->toBe(1600.0)
        ->and((float) $areaSalva->total_gastos_estim)->toBe(110.0)
        ->and((float) $areaSalva->total_gastos_reais)->toBe(85.0);
});

test('atualiza área existente com novos valores derivados', function () {
    $avaliacao = criarAgendaParaAreas();
    $area = AreaAtuacao::query()->create(['descricao' => 'TORQUE', 'observacoes' => '']);
    $avaliador = criarAvaliadorParaAreas();

    $areaAvaliada = AreaAvaliada::query()->create([
        'avaliacao_id' => $avaliacao->id,
        'area_atuacao_id' => $area->id,
        'avaliador_id' => $avaliador->id,
        'situacao' => 'AVALIADOR',
        'dias' => 1,
        'valor_avaliador' => 100,
        'total_gastos_estim' => 10,
        'total_gastos_reais' => 5,
    ]);

    $response = $this->actingAs(usuarioFuncionarioAvaliacao())->post(
        route('avaliacao-save-area', $areaAvaliada->uid),
        [
            'avaliacao_id' => $avaliacao->id,
            'area_atuacao_id' => $area->id,
            'avaliador_id' => $avaliador->id,
            'situacao' => 'AVALIADOR',
            'dias' => 2,
            'valor_dia' => '500,00',
            'valor_lider' => '50,00',
            'valor_estim_desloc' => '10,00',
            'valor_estim_alim' => '10,00',
            'valor_estim_hosped' => '10,00',
            'valor_estim_extras' => '10,00',
            'valor_real_desloc' => '5,00',
            'valor_real_alim' => '5,00',
            'valor_real_hosped' => '5,00',
            'valor_real_extras' => '5,00',
        ]
    );

    $response->assertRedirect();

    $areaAvaliada->refresh();

    expect((float) $areaAvaliada->valor_avaliador)->toBe(1050.0)
        ->and((float) $areaAvaliada->total_gastos_estim)->toBe(40.0)
        ->and((float) $areaAvaliada->total_gastos_reais)->toBe(20.0);
});

test('exclui área avaliada', function () {
    $avaliacao = criarAgendaParaAreas();
    $area = AreaAtuacao::query()->create(['descricao' => 'MASSA', 'observacoes' => '']);
    $avaliador = criarAvaliadorParaAreas();

    $areaAvaliada = AreaAvaliada::query()->create([
        'avaliacao_id' => $avaliacao->id,
        'area_atuacao_id' => $area->id,
        'avaliador_id' => $avaliador->id,
        'situacao' => 'AVALIADOR',
        'valor_avaliador' => 1000,
    ]);

    $response = $this->actingAs(usuarioFuncionarioAvaliacao())->post(
        route('avaliacao-delete-area', $areaAvaliada->uid)
    );

    $response->assertRedirect();

    expect(AreaAvaliada::query()->whereKey($areaAvaliada->id)->exists())->toBeFalse();
});

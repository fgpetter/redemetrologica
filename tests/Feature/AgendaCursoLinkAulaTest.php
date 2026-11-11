<?php

use App\Models\AgendaCursos;
use App\Models\Curso;
use App\Models\Instrutor;
use App\Models\Permission;
use App\Models\Pessoa;
use App\Models\User;

beforeEach(function () {
    $user = User::factory()->create();
    $permission = Permission::withoutEvents(fn () => Permission::query()->firstOrCreate(['permission' => 'funcionario']));
    $user->permissions()->syncWithoutDetaching([$permission->id]);
    $this->actingAs($user);

    $curso = Curso::query()->create(['descricao' => 'Curso teste', 'tipo_curso' => 'OFICIAL']);
    $pessoa = Pessoa::query()->create(['nome_razao' => 'Instrutor teste', 'cpf_cnpj' => '12345678901', 'tipo_pessoa' => 'PF']);
    $instrutor = Instrutor::query()->create(['pessoa_id' => $pessoa->id, 'situacao' => true]);
    $this->payload = [
        'status' => 'AGENDADO',
        'tipo_agendamento' => 'ONLINE',
        'curso_id' => $curso->id,
        'instrutor_id' => $instrutor->id,
        'data_inicio' => '2026-12-01',
    ];
});

it('persiste, atualiza e limpa o link da aula', function () {
    $this->post(route('agendamento-curso-create'), $this->payload + ['link_aula' => 'https://example.com/aula'])
        ->assertRedirect(route('agendamento-curso-index'))->assertSessionHasNoErrors();

    $agenda = AgendaCursos::query()->firstOrFail();
    expect($agenda->link_aula)->toBe('https://example.com/aula');

    $this->post(route('agendamento-curso-update', $agenda->uid), $this->payload + ['link_aula' => 'https://example.com/nova'])
        ->assertRedirect(route('agendamento-curso-index'))->assertSessionHasNoErrors();
    expect($agenda->fresh()->link_aula)->toBe('https://example.com/nova');

    $this->post(route('agendamento-curso-update', $agenda->uid), $this->payload + ['link_aula' => ''])
        ->assertRedirect(route('agendamento-curso-index'))->assertSessionHasNoErrors();
    expect($agenda->fresh()->link_aula)->toBeNull();
});

it('aceita agendamento sem link da aula', function () {
    $this->post(route('agendamento-curso-create'), $this->payload)
        ->assertRedirect(route('agendamento-curso-index'))->assertSessionHasNoErrors();
    expect(AgendaCursos::query()->firstOrFail()->link_aula)->toBeNull();
});

it('rejeita link da aula inválido ou longo demais', function (string $link) {
    $this->from('/anterior')->post(route('agendamento-curso-create'), $this->payload + ['link_aula' => $link])
        ->assertRedirect('/anterior')->assertSessionHasErrors('link_aula');
    expect(AgendaCursos::query()->count())->toBe(0);
})->with(['inválido' => 'não é uma URL', 'longo' => 'https://example.com/'.str_repeat('a', 256)]);

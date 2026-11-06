<?php

use App\Models\Laboratorio;
use App\Models\Permission;
use App\Models\User;
use Database\Factories\PessoaFactory;

beforeEach(function () {
    $user = User::factory()->create();
    $permission = Permission::withoutEvents(fn () => Permission::firstOrCreate(['permission' => 'funcionario']));
    $user->permissions()->syncWithoutDetaching([$permission->id]);
    $this->actingAs($user);
});

test('lista ações de laboratório de acordo com a pessoa', function () {
    $semLaboratorio = PessoaFactory::new()->create(['nome_razao' => 'Empresa sem laboratório']);
    $comLaboratorio = PessoaFactory::new()->create(['nome_razao' => 'Empresa com laboratório']);
    $pessoaFisica = PessoaFactory::new()->create(['tipo_pessoa' => 'PF']);
    $laboratorio = Laboratorio::create(['pessoa_id' => $comLaboratorio->id]);

    $response = $this->get(route('pessoa-index'));

    $response->assertSee('Adicionar laboratório')->assertSee('Editar laboratório')
        ->assertSee(route('laboratorio-insert', $laboratorio->uid))
        ->assertSee('value="'.$semLaboratorio->uid.'"', false)
        ->assertDontSee('value="'.$pessoaFisica->uid.'"', false)
        ->assertDontSee('value="'.$comLaboratorio->uid.'"', false);
});

test('cadastra laboratório e reutiliza vínculo em envio repetido', function () {
    $pessoa = PessoaFactory::new()->create();
    $response = $this->post(route('laboratorio-create'), ['pessoa_uid' => $pessoa->uid]);
    $laboratorio = Laboratorio::where('pessoa_id', $pessoa->id)->firstOrFail();
    $response->assertRedirect(route('laboratorio-insert', $laboratorio->uid))->assertSessionHas('success');

    $this->post(route('laboratorio-create'), ['pessoa_uid' => $pessoa->uid])
        ->assertRedirect(route('laboratorio-insert', $laboratorio->uid))->assertSessionHas('warning');
    expect(Laboratorio::where('pessoa_id', $pessoa->id)->count())->toBe(1);
});

test('rejeita pessoa física e pessoa excluída', function (string $tipo, bool $excluida) {
    $pessoa = PessoaFactory::new()->create(['tipo_pessoa' => $tipo]);
    if ($excluida) {
        $pessoa->delete();
    }

    $this->from(route('pessoa-index'))->post(route('laboratorio-create'), ['pessoa_uid' => $pessoa->uid])
        ->assertRedirect(route('pessoa-index'))->assertSessionHas('error');
    $this->assertDatabaseMissing('laboratorios', ['pessoa_id' => $pessoa->id]);
})->with([['PF', false], ['PJ', true]]);

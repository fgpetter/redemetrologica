<?php

use App\Models\Permission;
use App\Models\User;
use Database\Factories\PessoaFactory;
use Illuminate\Support\Facades\Mail;

function usuarioFuncionarioPessoaAssocia(): User
{
    $user = User::factory()->create();
    $permission = Permission::withoutEvents(function (): Permission {
        return Permission::query()->firstOrCreate(['permission' => 'funcionario']);
    });
    $user->permissions()->syncWithoutDetaching([$permission->id]);

    return $user;
}

test('nao cria usuario quando a pessoa nao tem email e exibe toast de erro', function () {
    Mail::fake();

    $pessoa = PessoaFactory::new()->create([
        'user_id' => null,
        'email' => null,
        'nome_razao' => 'Pessoa Sem Email',
    ]);

    $response = $this->actingAs(usuarioFuncionarioPessoaAssocia())
        ->from(route('pessoa-insert', $pessoa->uid))
        ->post(route('pessoa-associa-usuario', $pessoa->uid));

    $response->assertRedirect(route('pessoa-insert', $pessoa->uid));
    $response->assertSessionHas('error', 'É necessário preencher o e-mail da pessoa antes de criar o usuário.');

    expect($pessoa->fresh()->user_id)->toBeNull();
    expect(User::query()->where('name', 'Pessoa Sem Email')->exists())->toBeFalse();
});

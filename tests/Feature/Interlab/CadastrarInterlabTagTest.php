<?php

use App\Models\Permission;
use App\Models\User;

function usuarioFuncionarioCadastrarInterlab(): User
{
    $user = User::factory()->create();
    $permission = Permission::withoutEvents(function (): Permission {
        return Permission::query()->firstOrCreate(['permission' => 'funcionario']);
    });
    $user->permissions()->syncWithoutDetaching([$permission->id]);

    return $user;
}

test('rejeita tag com mais de 5 caracteres e informa o limite em portugues', function () {
    $response = $this->actingAs(usuarioFuncionarioCadastrarInterlab())
        ->from(route('interlab-insert'))
        ->post(route('interlab-create'), [
            'nome' => 'PEP Amostragem',
            'tipo' => 'INTERLABORATORIAL',
            'tag' => 'ABCDEF',
        ]);

    $response->assertRedirect(route('interlab-insert'));
    $response->assertSessionHasErrors([
        'tag' => 'O campo TAG deve ter no máximo 5 caracteres.',
    ]);

    $this->assertDatabaseMissing('interlabs', [
        'nome' => 'PEP Amostragem',
    ]);
});

test('cadastra interlab com tag de 5 caracteres', function () {
    $response = $this->actingAs(usuarioFuncionarioCadastrarInterlab())
        ->post(route('interlab-create'), [
            'nome' => 'PEP Amostragem',
            'tipo' => 'INTERLABORATORIAL',
            'tag' => 'ABCDE',
        ]);

    $response->assertRedirect(route('interlab-index'));
    $response->assertSessionHas('success', 'Interlab cadastrado com sucesso');

    $this->assertDatabaseHas('interlabs', [
        'nome' => 'PEP Amostragem',
        'tag' => 'ABCDE',
    ]);
});

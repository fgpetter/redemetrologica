<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PainelTomSelectViewsTest extends TestCase
{
    use DatabaseTransactions;

    private function usuarioFuncionario(): User
    {
        $user = User::factory()->create();

        $permission = Permission::withoutEvents(function (): Permission {
            return Permission::query()->firstOrCreate(['permission' => 'funcionario']);
        });

        $user->permissions()->syncWithoutDetaching([$permission->id]);

        return $user;
    }

    public function test_listagem_fornecedores_exibe_tom_select_sem_data_choices(): void
    {
        $response = $this->actingAs($this->usuarioFuncionario())->get(route('fornecedor-index'));

        $response->assertOk();
        $response->assertSee('id="tom-select"', false);
        $response->assertDontSee('data-choices', false);
    }

    public function test_formulario_inserir_lancamento_exibe_ids_tom_select_dedicados(): void
    {
        $response = $this->actingAs($this->usuarioFuncionario())->get(route('lancamento-financeiro-insert'));

        $response->assertOk();
        $response->assertSee('id="tom-select-lancamento-pessoa"', false);
        $response->assertSee('id="tom-select-lancamento-plano-conta"', false);
        $response->assertDontSee('data-choices', false);
    }
}

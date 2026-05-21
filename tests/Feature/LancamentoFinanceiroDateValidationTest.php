<?php

namespace Tests\Feature;

use App\Models\CentroCusto;
use App\Models\LancamentoFinanceiro;
use App\Models\Permission;
use App\Models\PlanoConta;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class LancamentoFinanceiroDateValidationTest extends TestCase
{
    use DatabaseTransactions;

    private function userComPermissaoFinanceiro(): User
    {
        $user = User::factory()->create();

        $permission = Permission::withoutEvents(function (): Permission {
            return Permission::query()->firstOrCreate(['permission' => 'funcionario']);
        });

        $user->permissions()->syncWithoutDetaching([$permission->id]);

        return $user;
    }

    /**
     * @return array{0: CentroCusto, 1: PlanoConta}
     */
    private function criarCentroEPlanoConta(): array
    {
        $centro = CentroCusto::create([
            'descricao' => 'CC-Date-'.uniqid(),
        ]);

        $plano = PlanoConta::create([
            'descricao' => 'PC-Date-'.uniqid(),
            'centro_custo_id' => $centro->id,
        ]);

        return [$centro, $plano];
    }

    public function test_store_rejeita_data_emissao_fora_do_formato_ymd(): void
    {
        $user = $this->userComPermissaoFinanceiro();

        [$centro, $plano] = $this->criarCentroEPlanoConta();

        $lancamento = LancamentoFinanceiro::factory()->create([
            'centro_custo_id' => $centro->id,
            'plano_conta_id' => $plano->id,
        ]);

        $countAntes = LancamentoFinanceiro::query()->count();

        $response = $this->actingAs($user)->post(route('lancamento-financeiro-store'), [
            'data_emissao' => '26666-03-12',
            'pessoa_id' => (string) $lancamento->pessoa_id,
            'centro_custo_id' => (string) $centro->id,
            'plano_conta_id' => (string) $plano->id,
            'tipo_lancamento' => 'CREDITO',
        ]);

        $response->assertSessionHasErrors('data_emissao');
        $this->assertSame($countAntes, LancamentoFinanceiro::query()->count());
    }
}

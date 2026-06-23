<?php

namespace Tests\Feature;

use App\Models\CentroCusto;
use App\Models\LancamentoFinanceiro;
use App\Models\Permission;
use App\Models\PlanoConta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DuplicarLancamentoFinanceiroTest extends TestCase
{
    use RefreshDatabase;

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
            'descricao' => 'CC-Dup-'.uniqid(),
        ]);

        $plano = PlanoConta::create([
            'descricao' => 'PC-Dup-'.uniqid(),
            'centro_custo_id' => $centro->id,
        ]);

        return [$centro, $plano];
    }

    /**
     * @return array<string, mixed>
     */
    private function payloadBaseDuplicar(LancamentoFinanceiro $original): array
    {
        return [
            'duplicar_lancamento' => '1',
            'lancamento_original_uid' => $original->uid,
            'data_emissao' => $original->data_emissao ? \Carbon\Carbon::parse($original->data_emissao)->format('Y-m-d') : '',
            'nota_fiscal' => $original->nota_fiscal ?? '',
            'consiliacao' => $original->consiliacao ?? '',
            'documento' => $original->documento ?? '',
            'pessoa_id' => (string) $original->pessoa_id,
            'centro_custo_id' => (string) $original->centro_custo_id,
            'plano_conta_id' => (string) $original->plano_conta_id,
            'historico' => $original->historico ?? '',
            'tipo_lancamento' => $original->tipo_lancamento,
            'valor' => number_format((float) $original->valor, 2, ',', '.'),
            'data_vencimento' => $original->data_vencimento ? \Carbon\Carbon::parse($original->data_vencimento)->format('Y-m-d') : '',
            'data_pagamento' => $original->data_pagamento ? \Carbon\Carbon::parse($original->data_pagamento)->format('Y-m-d') : '',
            'modalidade_pagamento_id' => $original->modalidade_pagamento_id ? (string) $original->modalidade_pagamento_id : '',
        ];
    }

    public function test_duplica_lancamento_sem_copiar_observacoes(): void
    {
        $user = $this->userComPermissaoFinanceiro();

        [$centro, $plano] = $this->criarCentroEPlanoConta();

        $original = LancamentoFinanceiro::factory()->create([
            'centro_custo_id' => $centro->id,
            'plano_conta_id' => $plano->id,
            'tipo_lancamento' => 'DEBITO',
            'historico' => 'Histórico original',
            'observacoes' => 'Observação sigilosa',
            'data_pagamento' => null,
            'status' => 'PROVISIONADO',
        ]);

        $payload = array_merge($this->payloadBaseDuplicar($original), [
            'historico' => 'Histórico da cópia',
            'observacoes' => 'Tentativa de injetar observação',
        ]);

        $countAntes = LancamentoFinanceiro::query()->count();

        $response = $this->actingAs($user)->post(route('lancamento-financeiro-store'), $payload);

        $response->assertRedirect(route('lancamento-financeiro-index'));
        $response->assertSessionHas('success');

        $this->assertSame($countAntes + 1, LancamentoFinanceiro::query()->count());

        $copia = LancamentoFinanceiro::query()
            ->where('historico', 'Histórico da cópia')
            ->where('id', '!=', $original->id)
            ->first();

        $this->assertNotNull($copia);
        $this->assertNull($copia->observacoes);
        $this->assertSame('Histórico da cópia', $copia->historico);
    }

    public function test_duplica_lancamento_com_data_pagamento_mantem_status_efetivado(): void
    {
        $user = $this->userComPermissaoFinanceiro();

        [$centro, $plano] = $this->criarCentroEPlanoConta();

        $original = LancamentoFinanceiro::factory()->create([
            'centro_custo_id' => $centro->id,
            'plano_conta_id' => $plano->id,
            'tipo_lancamento' => 'CREDITO',
            'data_pagamento' => now()->toDateString(),
            'status' => 'EFETIVADO',
        ]);

        $payload = $this->payloadBaseDuplicar($original);

        $response = $this->actingAs($user)->post(route('lancamento-financeiro-store'), $payload);

        $response->assertRedirect(route('lancamento-financeiro-index'));

        $copia = LancamentoFinanceiro::query()
            ->where('id', '!=', $original->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($copia);
        $this->assertSame('EFETIVADO', $copia->status);
        $this->assertNotNull($copia->data_pagamento);
    }

    public function test_erro_validacao_duplicacao_repassa_uid_para_reabrir_modal(): void
    {
        $user = $this->userComPermissaoFinanceiro();

        [$centro, $plano] = $this->criarCentroEPlanoConta();

        $original = LancamentoFinanceiro::factory()->create([
            'centro_custo_id' => $centro->id,
            'plano_conta_id' => $plano->id,
            'tipo_lancamento' => 'DEBITO',
        ]);

        $payload = $this->payloadBaseDuplicar($original);
        $payload['pessoa_id'] = '';

        $response = $this->actingAs($user)->post(route('lancamento-financeiro-store'), $payload);

        $response->assertRedirect();
        $response->assertSessionHasErrors('pessoa_id');
        $response->assertSessionHas('duplicar_lancamento_uid', $original->uid);
        $response->assertSessionHas('error');
    }
}

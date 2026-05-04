<?php

namespace Tests\Feature;

use App\Models\LancamentoFinanceiro;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class EditarLancamentosEmLoteTest extends TestCase
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

    public function test_atualiza_apenas_nota_fiscal_quando_demais_campos_vazios(): void
    {
        $user = $this->userComPermissaoFinanceiro();

        $l1 = LancamentoFinanceiro::factory()->create([
            'tipo_lancamento' => 'CREDITO',
            'data_pagamento' => now()->toDateString(),
            'status' => 'EFETIVADO',
            'nota_fiscal' => 'NF-ANTIGA',
            'consiliacao' => 'CONC-ORIGINAL',
        ]);

        $l2 = LancamentoFinanceiro::factory()->create([
            'tipo_lancamento' => 'CREDITO',
            'data_pagamento' => now()->subDay()->toDateString(),
            'status' => 'EFETIVADO',
            'nota_fiscal' => null,
            'consiliacao' => 'OUTRA-CONC',
        ]);

        $response = $this->actingAs($user)->post(route('lancamento-financeiro-batch-update'), [
            'uids' => [$l1->uid, $l2->uid],
            'nota_fiscal' => 'NF-NOVA',
            'consiliacao' => '',
            'data_pagamento' => '',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('lancamentos_financeiros', [
            'id' => $l1->id,
            'nota_fiscal' => 'NF-NOVA',
            'consiliacao' => 'CONC-ORIGINAL',
        ]);

        $this->assertDatabaseHas('lancamentos_financeiros', [
            'id' => $l2->id,
            'nota_fiscal' => 'NF-NOVA',
            'consiliacao' => 'OUTRA-CONC',
        ]);
    }

    public function test_atualiza_tres_campos(): void
    {
        $user = $this->userComPermissaoFinanceiro();

        $l = LancamentoFinanceiro::factory()->create([
            'tipo_lancamento' => 'CREDITO',
            'data_pagamento' => now()->toDateString(),
            'status' => 'EFETIVADO',
            'nota_fiscal' => null,
            'consiliacao' => null,
        ]);

        $novaData = now()->subDays(2)->toDateString();

        $response = $this->actingAs($user)->post(route('lancamento-financeiro-batch-update'), [
            'uids' => [$l->uid],
            'nota_fiscal' => 'NF-123',
            'consiliacao' => 'CONC-456',
            'data_pagamento' => $novaData,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('lancamentos_financeiros', [
            'id' => $l->id,
            'nota_fiscal' => 'NF-123',
            'consiliacao' => 'CONC-456',
            'data_pagamento' => $novaData,
            'status' => 'EFETIVADO',
        ]);
    }

    public function test_nao_atualiza_nada_quando_lista_inclui_debito(): void
    {
        $user = $this->userComPermissaoFinanceiro();

        $credito = LancamentoFinanceiro::factory()->create([
            'tipo_lancamento' => 'CREDITO',
            'data_pagamento' => now()->toDateString(),
            'status' => 'EFETIVADO',
            'nota_fiscal' => 'MANTER',
        ]);

        $debito = LancamentoFinanceiro::factory()->create([
            'tipo_lancamento' => 'DEBITO',
            'data_pagamento' => now()->toDateString(),
            'status' => 'EFETIVADO',
            'nota_fiscal' => 'DEB-NF',
        ]);

        $response = $this->actingAs($user)->post(route('lancamento-financeiro-batch-update'), [
            'uids' => [$credito->uid, $debito->uid],
            'nota_fiscal' => 'ALTERADO',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('lancamentos_financeiros', [
            'id' => $credito->id,
            'nota_fiscal' => 'MANTER',
        ]);

        $this->assertDatabaseHas('lancamentos_financeiros', [
            'id' => $debito->id,
            'nota_fiscal' => 'DEB-NF',
        ]);
    }

    public function test_validacao_falha_com_uid_inexistente(): void
    {
        $user = $this->userComPermissaoFinanceiro();

        $response = $this->actingAs($user)->post(route('lancamento-financeiro-batch-update'), [
            'uids' => ['uid-que-nao-existe-no-banco'],
            'nota_fiscal' => 'X',
        ]);

        $response->assertSessionHasErrors('uids.0');
    }

    public function test_todos_campos_vazios_nao_altera_registros(): void
    {
        $user = $this->userComPermissaoFinanceiro();

        $l = LancamentoFinanceiro::factory()->create([
            'tipo_lancamento' => 'CREDITO',
            'data_pagamento' => now()->toDateString(),
            'status' => 'EFETIVADO',
            'nota_fiscal' => 'ORIGINAL-NF',
            'consiliacao' => 'ORIGINAL-C',
        ]);

        $response = $this->actingAs($user)->post(route('lancamento-financeiro-batch-update'), [
            'uids' => [$l->uid],
            'nota_fiscal' => '',
            'consiliacao' => '',
            'data_pagamento' => '',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Nenhum dado para atualizar.');

        $this->assertDatabaseHas('lancamentos_financeiros', [
            'id' => $l->id,
            'nota_fiscal' => 'ORIGINAL-NF',
            'consiliacao' => 'ORIGINAL-C',
        ]);
    }

    public function test_atualiza_nota_fiscal_em_credito_provisionado_contas_a_receber(): void
    {
        $user = $this->userComPermissaoFinanceiro();

        $l1 = LancamentoFinanceiro::factory()->create([
            'tipo_lancamento' => 'CREDITO',
            'data_pagamento' => null,
            'status' => 'PROVISIONADO',
            'nota_fiscal' => 'NF-OLD',
            'consiliacao' => 'CONC-KEEP',
        ]);

        $l2 = LancamentoFinanceiro::factory()->create([
            'tipo_lancamento' => 'CREDITO',
            'data_pagamento' => null,
            'status' => 'PROVISIONADO',
            'nota_fiscal' => null,
            'consiliacao' => 'OUTRA',
        ]);

        $response = $this->actingAs($user)->post(route('lancamento-financeiro-batch-update'), [
            'uids' => [$l1->uid, $l2->uid],
            'nota_fiscal' => 'NF-LOTE-AR',
            'consiliacao' => '',
            'data_pagamento' => '',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('lancamentos_financeiros', [
            'id' => $l1->id,
            'nota_fiscal' => 'NF-LOTE-AR',
            'consiliacao' => 'CONC-KEEP',
            'status' => 'PROVISIONADO',
        ]);

        $this->assertDatabaseHas('lancamentos_financeiros', [
            'id' => $l2->id,
            'nota_fiscal' => 'NF-LOTE-AR',
            'consiliacao' => 'OUTRA',
            'status' => 'PROVISIONADO',
        ]);
    }

    public function test_atualiza_tres_campos_em_credito_provisionado_efetiva_status(): void
    {
        $user = $this->userComPermissaoFinanceiro();

        $l = LancamentoFinanceiro::factory()->create([
            'tipo_lancamento' => 'CREDITO',
            'data_pagamento' => null,
            'status' => 'PROVISIONADO',
            'nota_fiscal' => null,
            'consiliacao' => null,
        ]);

        $novaData = now()->subDay()->toDateString();

        $response = $this->actingAs($user)->post(route('lancamento-financeiro-batch-update'), [
            'uids' => [$l->uid],
            'nota_fiscal' => 'NF-999',
            'consiliacao' => 'CONC-888',
            'data_pagamento' => $novaData,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('lancamentos_financeiros', [
            'id' => $l->id,
            'nota_fiscal' => 'NF-999',
            'consiliacao' => 'CONC-888',
            'data_pagamento' => $novaData,
            'status' => 'EFETIVADO',
        ]);
    }
}

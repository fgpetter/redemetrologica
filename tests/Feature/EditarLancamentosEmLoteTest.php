<?php

namespace Tests\Feature;

use App\Actions\Financeiro\EditarLancamentosEmLoteAction;
use App\Models\LancamentoFinanceiro;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Tests\TestCase;

class EditarLancamentosEmLoteTest extends TestCase
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

        $resumos = [];
        Log::listen(function ($evento) use (&$resumos): void {
            if ($evento->message === 'fluxo.execucao' && ($evento->context['fluxo'] ?? null) === 'financeiro.editar_lote') {
                $resumos[] = $evento->context;
            }
        });

        $response = $this->actingAs($user)->post(route('lancamento-financeiro-batch-update'), [
            'uids' => [$l1->uid, $l2->uid],
            'nota_fiscal' => 'NF-NOVA',
            'consiliacao' => '',
            'data_pagamento' => '',
            'data_vencimento' => '',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertCount(1, $resumos);
        $this->assertSame([
            'processar' => 'concluido',
            'transacao_lote' => 'concluido',
            'selecionar_lancamentos' => 'concluido',
            'validar_lote' => 'concluido',
            'atualizar_lancamentos' => 'concluido',
        ], array_column($resumos[0]['etapas'], 'resultado', 'etapa'));

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

    public function test_correlaciona_excecao_tratada_ao_resumo_do_lote(): void
    {
        $user = $this->userComPermissaoFinanceiro();
        $lancamento = LancamentoFinanceiro::factory()->create(['tipo_lancamento' => 'CREDITO']);
        $exception = new RuntimeException('Falha sintética ao atualizar o lote');
        $this->mock(EditarLancamentosEmLoteAction::class)
            ->shouldReceive('execute')->once()->andThrow($exception);

        $correlacoes = [];
        Exceptions::reportable(function (RuntimeException $reported) use ($exception, &$correlacoes): void {
            if ($reported === $exception) {
                $correlacoes[] = Context::all();
            }
        });

        $resumos = [];
        Log::listen(function ($evento) use (&$resumos): void {
            if ($evento->message === 'fluxo.execucao' && ($evento->context['fluxo'] ?? null) === 'financeiro.editar_lote') {
                $resumos[] = $evento->context;
            }
        });

        $this->actingAs($user)->post(route('lancamento-financeiro-batch-update'), [
            'uids' => [$lancamento->uid],
            'nota_fiscal' => 'NF-NOVA',
        ])->assertRedirect()->assertSessionHas('error', 'Não foi possível atualizar os lançamentos.');

        $this->assertCount(1, $resumos);
        $this->assertCount(1, $correlacoes);
        $this->assertSame('falha_definitiva', $resumos[0]['resultado']);
        $this->assertSame('financeiro.editar_lote', $correlacoes[0]['fluxo']);
        $this->assertSame($resumos[0]['execucao_id'], $correlacoes[0]['execucao_id']);
        $this->assertNull(Context::get('execucao_id'));
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
            'data_vencimento' => '',
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
            'data_vencimento' => '',
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

    public function test_atualiza_data_vencimento_em_credito_sem_alterar_quando_campo_vazio(): void
    {
        $user = $this->userComPermissaoFinanceiro();

        $vencimentoOriginal = now()->addDays(10)->toDateString();

        $l = LancamentoFinanceiro::factory()->create([
            'tipo_lancamento' => 'CREDITO',
            'data_pagamento' => now()->toDateString(),
            'status' => 'EFETIVADO',
            'data_vencimento' => $vencimentoOriginal,
            'nota_fiscal' => 'NF-KEEP',
        ]);

        $response = $this->actingAs($user)->post(route('lancamento-financeiro-batch-update'), [
            'uids' => [$l->uid],
            'nota_fiscal' => 'NF-ATUALIZADA',
            'consiliacao' => '',
            'data_pagamento' => '',
            'data_vencimento' => '',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('lancamentos_financeiros', [
            'id' => $l->id,
            'nota_fiscal' => 'NF-ATUALIZADA',
            'data_vencimento' => $vencimentoOriginal,
        ]);
    }

    public function test_atualiza_data_vencimento_em_credito_quando_preenchida(): void
    {
        $user = $this->userComPermissaoFinanceiro();

        $l = LancamentoFinanceiro::factory()->create([
            'tipo_lancamento' => 'CREDITO',
            'data_pagamento' => now()->toDateString(),
            'status' => 'EFETIVADO',
            'data_vencimento' => now()->addDay()->toDateString(),
            'nota_fiscal' => 'X',
        ]);

        $novaVencimento = now()->addDays(30)->toDateString();

        $response = $this->actingAs($user)->post(route('lancamento-financeiro-batch-update'), [
            'uids' => [$l->uid],
            'nota_fiscal' => '',
            'consiliacao' => '',
            'data_pagamento' => '',
            'data_vencimento' => $novaVencimento,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('lancamentos_financeiros', [
            'id' => $l->id,
            'data_vencimento' => $novaVencimento,
            'nota_fiscal' => 'X',
        ]);
    }

    public function test_atualiza_lote_homogeneo_de_debitos(): void
    {
        $user = $this->userComPermissaoFinanceiro();

        $d1 = LancamentoFinanceiro::factory()->create([
            'tipo_lancamento' => 'DEBITO',
            'data_pagamento' => now()->toDateString(),
            'status' => 'EFETIVADO',
            'nota_fiscal' => 'A',
        ]);

        $d2 = LancamentoFinanceiro::factory()->create([
            'tipo_lancamento' => 'DEBITO',
            'data_pagamento' => now()->subDay()->toDateString(),
            'status' => 'EFETIVADO',
            'nota_fiscal' => 'B',
        ]);

        $response = $this->actingAs($user)->post(route('lancamento-financeiro-batch-update'), [
            'uids' => [$d1->uid, $d2->uid],
            'nota_fiscal' => 'NF-DEB-LOTE',
            'consiliacao' => '',
            'data_pagamento' => '',
            'data_vencimento' => '',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('lancamentos_financeiros', [
            'id' => $d1->id,
            'nota_fiscal' => 'NF-DEB-LOTE',
        ]);

        $this->assertDatabaseHas('lancamentos_financeiros', [
            'id' => $d2->id,
            'nota_fiscal' => 'NF-DEB-LOTE',
        ]);
    }
}

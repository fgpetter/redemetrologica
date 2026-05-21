<?php

namespace Tests\Feature\InscricaoInterlab;

use App\Livewire\Interlab\ListParticipantes;
use App\Models\User;
use Database\Factories\InterlabInscritoFactory;
use Database\Factories\LancamentoFinanceiroFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SalvaInscritoInterlabTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var User $user */
        $user = User::factory()->createOne();

        $this->actingAs($user);
    }

    public function test_atualiza_valor_do_inscrito(): void
    {
        $inscrito = InterlabInscritoFactory::new()->create([
            'valor' => null,
            'lancamento_financeiro_id' => null,
        ]);

        Livewire::test(ListParticipantes::class, ['idinterlab' => $inscrito->agenda_interlab_id])
            ->call('atualizarValor', $inscrito->id, 1500)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('interlab_inscritos', [
            'id' => $inscrito->id,
            'valor' => 1500.00,
        ]);
    }

    public function test_cria_lancamento_quando_inscrito_nao_possui_vinculo(): void
    {
        $inscrito = InterlabInscritoFactory::new()->create([
            'valor' => null,
            'lancamento_financeiro_id' => null,
        ]);

        Livewire::test(ListParticipantes::class, ['idinterlab' => $inscrito->agenda_interlab_id])
            ->call('atualizarValor', $inscrito->id, 1500)
            ->assertHasNoErrors();

        $inscrito->refresh();

        $this->assertNotNull($inscrito->lancamento_financeiro_id);
        $this->assertDatabaseCount('lancamentos_financeiros', 1);
        $this->assertDatabaseHas('lancamentos_financeiros', [
            'id' => $inscrito->lancamento_financeiro_id,
            'valor' => 1500.00,
            'status' => 'PROVISIONADO',
            'agenda_interlab_id' => $inscrito->agenda_interlab_id,
        ]);
    }

    public function test_atualiza_lancamento_existente_sem_criar_novo_registro(): void
    {
        $inscrito = InterlabInscritoFactory::new()->create([
            'valor' => 100.00,
            'lancamento_financeiro_id' => null,
        ]);

        $lancamentoExistente = LancamentoFinanceiroFactory::new()->create([
            'pessoa_id' => $inscrito->empresa_id,
            'agenda_interlab_id' => $inscrito->agenda_interlab_id,
            'valor' => 100.00,
        ]);

        $inscrito->update([
            'lancamento_financeiro_id' => $lancamentoExistente->id,
        ]);

        Livewire::test(ListParticipantes::class, ['idinterlab' => $inscrito->agenda_interlab_id])
            ->call('atualizarValor', $inscrito->id, 2000)
            ->assertHasNoErrors();

        $this->assertDatabaseCount('lancamentos_financeiros', 1);
        $this->assertDatabaseHas('lancamentos_financeiros', [
            'id' => $lancamentoExistente->id,
            'valor' => 2000.00,
            'agenda_interlab_id' => $inscrito->agenda_interlab_id,
            'pessoa_id' => $inscrito->empresa_id,
        ]);
    }

    public function test_nao_gera_lancamento_quando_valor_esta_zero(): void
    {
        $inscrito = InterlabInscritoFactory::new()->create([
            'valor' => null,
            'lancamento_financeiro_id' => null,
        ]);

        Livewire::test(ListParticipantes::class, ['idinterlab' => $inscrito->agenda_interlab_id])
            ->call('atualizarValor', $inscrito->id, 0)
            ->assertHasNoErrors();

        $this->assertDatabaseCount('lancamentos_financeiros', 0);
        $this->assertDatabaseHas('interlab_inscritos', [
            'id' => $inscrito->id,
            'valor' => 0,
        ]);
    }
}

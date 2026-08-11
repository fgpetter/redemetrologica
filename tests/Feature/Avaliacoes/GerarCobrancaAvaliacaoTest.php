<?php

namespace Tests\Feature\Avaliacoes;

use App\Actions\Financeiro\GerarLancamentoAvaliacaoAction;
use App\Mail\LancamentoAvaliacaoNotification;
use App\Models\AgendaAvaliacao;
use App\Models\AreaAtuacao;
use App\Models\AreaAvaliada;
use App\Models\Avaliador;
use App\Models\CentroCusto;
use App\Models\Laboratorio;
use App\Models\LancamentoFinanceiro;
use App\Models\Permission;
use App\Models\PlanoConta;
use App\Models\User;
use Database\Factories\PessoaFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class GerarCobrancaAvaliacaoTest extends TestCase
{
    use RefreshDatabase;

    private function createFuncionarioUser(): User
    {
        $user = User::factory()->create();
        $permission = Permission::withoutEvents(function (): Permission {
            return Permission::query()->firstOrCreate(['permission' => 'funcionario']);
        });
        $user->permissions()->syncWithoutDetaching([$permission->id]);

        return $user;
    }

    private function createAvaliacao(array $atributos = []): AgendaAvaliacao
    {
        $pessoa = PessoaFactory::new()->create([
            'nome_razao' => 'Laboratorio Teste Ltda',
            'tipo_pessoa' => 'PJ',
        ]);

        $laboratorio = Laboratorio::query()->create([
            'pessoa_id' => $pessoa->id,
            'nome_laboratorio' => 'Laboratorio Teste',
            'contato' => 'Contato',
            'telefone' => '11999999999',
            'email' => 'lab-cobranca@example.com',
            'responsavel_tecnico' => 'Responsável',
        ]);

        return AgendaAvaliacao::query()->create(array_merge([
            'laboratorio_id' => $laboratorio->id,
            'data_inicio' => now()->toDateString(),
            'perc_lucro' => 15,
            'carta_reconhecimento' => 0,
        ], $atributos));
    }

    private function criarAreaComValorProposta(AgendaAvaliacao $avaliacao): void
    {
        $area = AreaAtuacao::query()->create(['descricao' => 'DIMENSIONAL', 'observacoes' => '']);
        $pessoa = PessoaFactory::new()->create([
            'tipo_pessoa' => 'PF',
            'cpf_cnpj' => fake()->unique()->numerify('###########'),
            'nome_razao' => 'Avaliador A',
        ]);
        $avaliador = Avaliador::query()->create([
            'pessoa_id' => $pessoa->id,
            'situacao' => 'AVALIADOR',
        ]);

        AreaAvaliada::query()->create([
            'avaliacao_id' => $avaliacao->id,
            'area_atuacao_id' => $area->id,
            'avaliador_id' => $avaliador->id,
            'situacao' => 'AVALIADOR',
            'num_ensaios' => 2,
            'dias' => 1.5,
            'valor_avaliador' => 1800,
            'total_gastos_estim' => 350,
            'total_gastos_reais' => 175,
        ]);
    }

    public function test_carta_sim_com_valor_proposta_cria_lancamento_e_notifica_financeiro(): void
    {
        Mail::fake();

        $avaliacao = $this->createAvaliacao();
        $this->criarAreaComValorProposta($avaliacao);
        $user = $this->createFuncionarioUser();

        $response = $this->actingAs($user)->post(route('avaliacao-update', $avaliacao->uid), [
            'carta_reconhecimento' => '1',
        ]);

        $response->assertSessionHas('success');

        $avaliacao->refresh();
        $this->assertEquals(1, $avaliacao->carta_reconhecimento);

        $this->assertDatabaseCount('lancamentos_financeiros', 1);
        $this->assertDatabaseHas('lancamentos_financeiros', [
            'agenda_avaliacao_id' => $avaliacao->id,
            'pessoa_id' => $avaliacao->laboratorio->pessoa_id,
            'tipo_lancamento' => 'CREDITO',
            'status' => 'PROVISIONADO',
            'centro_custo_id' => CentroCusto::ID_AVALIACAO,
            'plano_conta_id' => PlanoConta::ID_RECEITA_PRESTACAO_SERVICOS,
            'valor' => 2533.95,
        ]);

        Mail::assertQueued(LancamentoAvaliacaoNotification::class, function ($mail) {
            return $mail->hasTo('financeiro@redemetrologica.com.br');
        });
    }

    public function test_carta_sim_sem_valor_proposta_nao_altera_status_nem_cria_lancamento(): void
    {
        Mail::fake();

        $avaliacao = $this->createAvaliacao();
        $user = $this->createFuncionarioUser();

        $response = $this->actingAs($user)->post(route('avaliacao-update', $avaliacao->uid), [
            'carta_reconhecimento' => '1',
        ]);

        $response->assertSessionHas('error');

        $avaliacao->refresh();
        $this->assertEquals(0, $avaliacao->carta_reconhecimento);
        $this->assertDatabaseCount('lancamentos_financeiros', 0);
        Mail::assertNothingQueued();
    }

    public function test_resalvar_carta_sim_com_lancamento_existente_nao_cria_nem_altera(): void
    {
        Mail::fake();

        $avaliacao = $this->createAvaliacao(['carta_reconhecimento' => 1]);
        $this->criarAreaComValorProposta($avaliacao);
        app(GerarLancamentoAvaliacaoAction::class)->execute($avaliacao);
        $lancamentoOriginal = LancamentoFinanceiro::where('agenda_avaliacao_id', $avaliacao->id)->firstOrFail();

        $user = $this->createFuncionarioUser();

        $response = $this->actingAs($user)->post(route('avaliacao-update', $avaliacao->uid), [
            'carta_reconhecimento' => '1',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseCount('lancamentos_financeiros', 1);
        $this->assertEquals($lancamentoOriginal->valor, LancamentoFinanceiro::find($lancamentoOriginal->id)->valor);
        Mail::assertQueued(LancamentoAvaliacaoNotification::class, 1);
    }

    public function test_carta_nao_com_lancamento_existente_bloqueia_alteracao(): void
    {
        Mail::fake();

        $avaliacao = $this->createAvaliacao(['carta_reconhecimento' => 1]);
        $this->criarAreaComValorProposta($avaliacao);
        app(GerarLancamentoAvaliacaoAction::class)->execute($avaliacao);

        $user = $this->createFuncionarioUser();

        $response = $this->actingAs($user)->post(route('avaliacao-update', $avaliacao->uid), [
            'carta_reconhecimento' => '0',
        ]);

        $response->assertSessionHas('error', 'Já há um lançamento financeiro para essa avaliação. Esse status só pode ser trocado se o lançamento for excluido.');

        $avaliacao->refresh();
        $this->assertEquals(1, $avaliacao->carta_reconhecimento);
        $this->assertDatabaseCount('lancamentos_financeiros', 1);
    }
}

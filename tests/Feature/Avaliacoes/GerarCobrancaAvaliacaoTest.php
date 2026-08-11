<?php

namespace Tests\Feature\Avaliacoes;

use App\Actions\Financeiro\GerarLancamentoAvaliacaoAction;
use App\Mail\LancamentoAvaliacaoNotification;
use App\Models\AgendaAvaliacao;
use App\Models\CentroCusto;
use App\Models\Laboratorio;
use App\Models\LancamentoFinanceiro;
use App\Models\Pessoa;
use App\Models\PlanoConta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class GerarCobrancaAvaliacaoTest extends TestCase
{
    use RefreshDatabase;

    private function createFuncionarioUser(): User
    {
        $user = User::query()->create([
            'name' => 'Funcionário Teste',
            'email' => 'func-'.Str::random(8).'@example.com',
            'password' => bcrypt('password'),
            'temporary_password' => false,
        ]);

        $permissionId = DB::table('permissions')->where('permission', 'funcionario')->value('id');
        if ($permissionId === null) {
            $permissionId = DB::table('permissions')->insertGetId([
                'permission' => 'funcionario',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('permission_user')->insert([
            'permission_id' => $permissionId,
            'user_id' => $user->id,
        ]);

        return $user->fresh();
    }

    private function createAvaliacao(array $atributos = []): AgendaAvaliacao
    {
        $pessoa = Pessoa::query()->create([
            'nome_razao' => 'Laboratorio Teste Ltda',
            'cpf_cnpj' => str_pad((string) random_int(10000000000000, 99999999999999), 14, '0', STR_PAD_LEFT),
            'tipo_pessoa' => 'PJ',
        ]);

        $laboratorio = Laboratorio::query()->create([
            'pessoa_id' => $pessoa->id,
            'nome_laboratorio' => 'Laboratorio Teste',
        ]);

        return AgendaAvaliacao::query()->create(array_merge([
            'laboratorio_id' => $laboratorio->id,
            'data_inicio' => now()->toDateString(),
            'valor_proposta' => 1500,
            'carta_reconhecimento' => 0,
        ], $atributos));
    }

    public function test_carta_sim_com_valor_proposta_cria_lancamento_e_notifica_financeiro(): void
    {
        Mail::fake();

        $avaliacao = $this->createAvaliacao();
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
            'valor' => 1500,
        ]);

        Mail::assertQueued(LancamentoAvaliacaoNotification::class, function ($mail) {
            return $mail->hasTo('financeiro@redemetrologica.com.br');
        });
    }

    public function test_carta_sim_sem_valor_proposta_nao_altera_status_nem_cria_lancamento(): void
    {
        Mail::fake();

        $avaliacao = $this->createAvaliacao(['valor_proposta' => null]);
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

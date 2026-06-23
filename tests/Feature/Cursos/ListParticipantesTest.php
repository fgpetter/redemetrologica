<?php

namespace Tests\Feature\Cursos;

use App\Livewire\Cursos\ListParticipantes;
use App\Models\AgendaCursos;
use App\Models\CentroCusto;
use App\Models\Curso;
use App\Models\Instrutor;
use App\Models\LancamentoFinanceiro;
use App\Models\Pessoa;
use App\Models\PlanoConta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class ListParticipantesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedCentroCustoEPlanoConta();
    }

    public function test_save_inscrito_in_company_nao_altera_valor_do_lancamento_existente(): void
    {
        $empresa = $this->createEmpresa();
        $agenda = $this->createAgendaInCompany($empresa, valorOrcamento: 985.00);
        $user = $this->createFuncionarioUser();

        $lancamento = LancamentoFinanceiro::query()->create([
            'pessoa_id' => $empresa->id,
            'agenda_curso_id' => $agenda->id,
            'historico' => 'Curso In Company - '.$agenda->curso->descricao,
            'valor' => 985.00,
            'centro_custo_id' => CentroCusto::ID_TREINAMENTO,
            'plano_conta_id' => PlanoConta::ID_RECEITA_PRESTACAO_SERVICOS,
            'tipo_lancamento' => 'CREDITO',
            'data_emissao' => now(),
            'status' => 'PROVISIONADO',
        ]);

        $this->actingAs($user);

        Livewire::test(ListParticipantes::class, ['agendacurso' => $agenda])
            ->set('nome', 'Participante In Company')
            ->set('email', 'participante@example.com')
            ->call('saveInscrito')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('lancamentos_financeiros', [
            'id' => $lancamento->id,
            'valor' => '985.00',
        ]);

        $this->assertDatabaseHas('curso_inscritos', [
            'nome' => 'Participante In Company',
            'valor' => null,
            'lancamento_financeiro_id' => $lancamento->id,
        ]);
    }

    public function test_save_inscrito_in_company_nao_atualiza_valor_do_lancamento_mesmo_sem_valor_orcamento(): void
    {
        $empresa = $this->createEmpresa();
        $agenda = $this->createAgendaInCompany($empresa, valorOrcamento: 0, investimento: 200.00);
        $user = $this->createFuncionarioUser();

        $lancamento = LancamentoFinanceiro::query()->create([
            'pessoa_id' => $empresa->id,
            'agenda_curso_id' => $agenda->id,
            'historico' => 'Inscrição no curso - '.$agenda->curso->descricao,
            'valor' => 200.00,
            'centro_custo_id' => CentroCusto::ID_TREINAMENTO,
            'plano_conta_id' => PlanoConta::ID_RECEITA_PRESTACAO_SERVICOS,
            'tipo_lancamento' => 'CREDITO',
            'data_emissao' => now(),
            'status' => 'PROVISIONADO',
        ]);

        \App\Models\CursoInscrito::query()->create([
            'pessoa_id' => $user->pessoa->id,
            'agenda_curso_id' => $agenda->id,
            'empresa_id' => $empresa->id,
            'nome' => 'Primeiro Participante',
            'email' => 'primeiro@example.com',
            'valor' => null,
            'lancamento_financeiro_id' => $lancamento->id,
            'data_inscricao' => now(),
        ]);

        $this->actingAs($user);

        Livewire::test(ListParticipantes::class, ['agendacurso' => $agenda])
            ->set('nome', 'Segundo Participante')
            ->set('email', 'segundo@example.com')
            ->call('saveInscrito')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('lancamentos_financeiros', [
            'id' => $lancamento->id,
            'valor' => '200.00',
        ]);
    }

    public function test_save_inscrito_in_company_sem_lancamento_nao_cria_lancamento(): void
    {
        $empresa = $this->createEmpresa();
        $agenda = $this->createAgendaInCompany($empresa, valorOrcamento: 0, investimento: 300.00);
        $user = $this->createFuncionarioUser();

        $this->actingAs($user);

        Livewire::test(ListParticipantes::class, ['agendacurso' => $agenda])
            ->set('nome', 'Primeiro Participante')
            ->set('email', 'primeiro@example.com')
            ->call('saveInscrito')
            ->assertHasNoErrors();

        $this->assertDatabaseCount('lancamentos_financeiros', 0);
        $this->assertDatabaseCount('curso_inscritos', 1);

        $inscrito = \App\Models\CursoInscrito::query()->first();
        $this->assertNull($inscrito->lancamento_financeiro_id);
        $this->assertNull($inscrito->valor);
    }

    private function createEmpresa(): Pessoa
    {
        return Pessoa::query()->create([
            'nome_razao' => 'Empresa Teste LTDA',
            'cpf_cnpj' => str_pad((string) random_int(10000000000000, 99999999999999), 14, '0', STR_PAD_LEFT),
            'tipo_pessoa' => 'PJ',
            'associado' => 0,
        ]);
    }

    private function createAgendaInCompany(Pessoa $empresa, float $valorOrcamento = 0, ?float $investimento = null): AgendaCursos
    {
        $curso = Curso::query()->create([
            'descricao' => 'Curso IN-COMPANY Teste',
            'tipo_curso' => 'OFICIAL',
            'carga_horaria' => 8,
            'conteudo_programatico' => 'Conteúdo programático de teste',
        ]);

        $pessoaInstrutor = Pessoa::query()->create([
            'nome_razao' => 'Instrutor Teste',
            'cpf_cnpj' => str_pad((string) random_int(10000000000, 99999999999), 11, '0', STR_PAD_LEFT),
            'tipo_pessoa' => 'PF',
        ]);

        $instrutor = Instrutor::query()->create([
            'pessoa_id' => $pessoaInstrutor->id,
            'situacao' => true,
        ]);

        return AgendaCursos::query()->create([
            'uid' => (string) Str::uuid(),
            'curso_id' => $curso->id,
            'instrutor_id' => $instrutor->id,
            'empresa_id' => $empresa->id,
            'status' => 'AGENDADO',
            'tipo_agendamento' => 'IN-COMPANY',
            'inscricoes' => 0,
            'site' => 0,
            'investimento' => $investimento,
            'investimento_associado' => $investimento,
            'valor_orcamento' => $valorOrcamento > 0 ? $valorOrcamento : null,
            'status_proposta' => $valorOrcamento > 0 ? 'APROVADA' : null,
            'data_inicio' => now()->addWeek(),
            'data_fim' => now()->addWeek(),
        ]);
    }

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

        Pessoa::query()->create([
            'user_id' => $user->id,
            'nome_razao' => 'Funcionário PF',
            'cpf_cnpj' => str_pad((string) random_int(10000000000, 99999999999), 11, '0', STR_PAD_LEFT),
            'tipo_pessoa' => 'PF',
        ]);

        return $user->fresh('pessoa');
    }

    private function seedCentroCustoEPlanoConta(): void
    {
        CentroCusto::query()->firstOrCreate(
            ['id' => CentroCusto::ID_TREINAMENTO],
            ['descricao' => 'Treinamento']
        );

        PlanoConta::query()->firstOrCreate(
            ['id' => PlanoConta::ID_RECEITA_PRESTACAO_SERVICOS],
            [
                'descricao' => 'Receita Prestação de Serviços',
                'centro_custo_id' => CentroCusto::ID_TREINAMENTO,
            ]
        );
    }
}

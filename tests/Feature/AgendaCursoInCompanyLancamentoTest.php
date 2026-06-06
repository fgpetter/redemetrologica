<?php

namespace Tests\Feature;

use App\Models\AgendaCursos;
use App\Models\CentroCusto;
use App\Models\Curso;
use App\Models\Instrutor;
use App\Models\Pessoa;
use App\Models\PlanoConta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class AgendaCursoInCompanyLancamentoTest extends TestCase
{
    use RefreshDatabase;

    private function uniqueCnpjDigits(): string
    {
        return str_pad((string) random_int(10000000000000, 99999999999999), 14, '0', STR_PAD_LEFT);
    }

    private function uniqueCpfDigits(): string
    {
        return str_pad((string) random_int(10000000000, 99999999999), 11, '0', STR_PAD_LEFT);
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

        return $user->fresh();
    }

    /**
     * @return array{curso: Curso, instrutor: Instrutor, empresa: Pessoa}
     */
    private function createDependencias(): array
    {
        $curso = Curso::query()->create([
            'descricao' => 'Curso In Company',
            'tipo_curso' => 'OFICIAL',
        ]);

        $pessoaInstrutor = Pessoa::query()->create([
            'nome_razao' => 'Instrutor In Company',
            'cpf_cnpj' => $this->uniqueCpfDigits(),
            'tipo_pessoa' => 'PF',
        ]);

        $instrutor = Instrutor::query()->create([
            'pessoa_id' => $pessoaInstrutor->id,
            'situacao' => true,
        ]);

        $empresa = Pessoa::query()->create([
            'nome_razao' => 'Empresa In Company',
            'cpf_cnpj' => $this->uniqueCnpjDigits(),
            'tipo_pessoa' => 'PJ',
        ]);

        return compact('curso', 'instrutor', 'empresa');
    }

    /**
     * @return array<string, mixed>
     */
    private function payloadBase(Curso $curso, Instrutor $instrutor, Pessoa $empresa): array
    {
        return [
            'status' => 'AGENDADO',
            'tipo_agendamento' => 'IN-COMPANY',
            'curso_id' => $curso->id,
            'instrutor_id' => $instrutor->id,
            'empresa_id' => $empresa->id,
            'data_inicio' => now()->toDateString(),
            'status_proposta' => 'APROVADA',
            'valor_orcamento' => '5.000,00',
        ];
    }

    public function test_create_gera_lancamento_com_tipo_credito_quando_proposta_aprovada(): void
    {
        ['curso' => $curso, 'instrutor' => $instrutor, 'empresa' => $empresa] = $this->createDependencias();
        $user = $this->createFuncionarioUser();

        $response = $this->actingAs($user)->post(
            route('agendamento-curso-in-company-create'),
            $this->payloadBase($curso, $instrutor, $empresa)
        );

        $response->assertRedirect(route('agendamento-curso-index'));

        $agenda = AgendaCursos::query()->where('empresa_id', $empresa->id)->first();
        $this->assertNotNull($agenda);

        $this->assertDatabaseHas('lancamentos_financeiros', [
            'pessoa_id' => $empresa->id,
            'agenda_curso_id' => $agenda->id,
            'tipo_lancamento' => 'CREDITO',
            'status' => 'PROVISIONADO',
            'plano_conta_id' => PlanoConta::ID_RECEITA_PRESTACAO_SERVICOS,
            'centro_custo_id' => CentroCusto::ID_TREINAMENTO,
            'valor' => 5000.00,
        ]);
    }

    public function test_update_gera_lancamento_com_tipo_credito_quando_proposta_aprovada(): void
    {
        ['curso' => $curso, 'instrutor' => $instrutor, 'empresa' => $empresa] = $this->createDependencias();
        $user = $this->createFuncionarioUser();

        $agenda = AgendaCursos::query()->create([
            'uid' => (string) Str::uuid(),
            'curso_id' => $curso->id,
            'instrutor_id' => $instrutor->id,
            'empresa_id' => $empresa->id,
            'status' => 'AGENDADO',
            'tipo_agendamento' => 'IN-COMPANY',
            'data_inicio' => now()->toDateString(),
            'inscricoes' => 0,
            'site' => 0,
            'destaque' => 0,
        ]);

        $response = $this->actingAs($user)->post(
            route('agendamento-curso-in-company-update', ['agendacurso' => $agenda->uid]),
            $this->payloadBase($curso, $instrutor, $empresa)
        );

        $response->assertRedirect(route('agendamento-curso-index'));

        $this->assertDatabaseHas('lancamentos_financeiros', [
            'pessoa_id' => $empresa->id,
            'agenda_curso_id' => $agenda->id,
            'tipo_lancamento' => 'CREDITO',
            'valor' => 5000.00,
        ]);
    }
}

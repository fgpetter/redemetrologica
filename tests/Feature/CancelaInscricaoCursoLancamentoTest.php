<?php

namespace Tests\Feature;

use App\Models\AgendaCursos;
use App\Models\Curso;
use App\Models\CursoInscrito;
use App\Models\Instrutor;
use App\Models\LancamentoFinanceiro;
use App\Models\Pessoa;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class CancelaInscricaoCursoLancamentoTest extends TestCase
{
    use DatabaseTransactions;

    private function uniqueCpfDigits(): string
    {
        return str_pad((string) random_int(10000000000, 99999999999), 11, '0', STR_PAD_LEFT);
    }

    private function uniqueCnpjDigits(): string
    {
        return str_pad((string) random_int(10000000000000, 99999999999999), 14, '0', STR_PAD_LEFT);
    }

    private function createAgendaCurso(): AgendaCursos
    {
        $curso = Curso::query()->create([
            'descricao' => 'Curso teste cancelamento',
            'tipo_curso' => 'OFICIAL',
        ]);

        $pessoaInstrutor = Pessoa::query()->create([
            'nome_razao' => 'Instrutor Teste',
            'cpf_cnpj' => $this->uniqueCpfDigits(),
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
            'status' => 'AGENDADO',
            'tipo_agendamento' => 'ONLINE',
            'inscricoes' => 1,
            'investimento' => 100,
            'investimento_associado' => 80,
        ]);
    }

    private function createAuthenticatedUser(): User
    {
        $user = User::query()->create([
            'name' => 'Usuário Teste',
            'email' => 'user-'.Str::random(8).'@example.com',
            'password' => bcrypt('password'),
            'temporary_password' => false,
        ]);

        return $user->fresh();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createCursoInscrito(array $attributes): CursoInscrito
    {
        if (! isset($attributes['pessoa_id'])) {
            $attributes['pessoa_id'] = Pessoa::query()->create([
                'nome_razao' => 'Participante',
                'cpf_cnpj' => $this->uniqueCpfDigits(),
                'tipo_pessoa' => 'PF',
            ])->id;
        }

        $attributes['valor'] = $attributes['valor'] ?? 100;
        $attributes['data_inscricao'] = $attributes['data_inscricao'] ?? now();

        if (Schema::hasColumn('curso_inscritos', 'nome')) {
            $attributes['nome'] = $attributes['nome'] ?? 'Participante Teste';
            $attributes['email'] = $attributes['email'] ?? 'participante@example.com';
            $attributes['telefone'] = $attributes['telefone'] ?? '11999998888';
        }

        $columns = array_flip(Schema::getColumnListing('curso_inscritos'));
        $filtered = array_intersect_key($attributes, $columns);

        return CursoInscrito::query()->create($filtered);
    }

    private function createLancamentoParaEmpresaCurso(Pessoa $empresa, AgendaCursos $agenda, float|int|string $valor): LancamentoFinanceiro
    {
        return LancamentoFinanceiro::query()->create([
            'tipo_lancamento' => 'CREDITO',
            'status' => 'PROVISIONADO',
            'pessoa_id' => $empresa->id,
            'agenda_curso_id' => $agenda->id,
            'valor' => $valor,
            'historico' => 'Inscrição teste',
        ]);
    }

    public function test_cancela_ultimo_inscrito_pj_remove_lancamento_financeiro(): void
    {
        $agenda = $this->createAgendaCurso();
        $user = $this->createAuthenticatedUser();

        $empresa = Pessoa::query()->create([
            'nome_razao' => 'Empresa PJ',
            'cpf_cnpj' => $this->uniqueCnpjDigits(),
            'tipo_pessoa' => 'PJ',
        ]);

        $lancamento = $this->createLancamentoParaEmpresaCurso($empresa, $agenda, 100);

        $pessoaParticipante = Pessoa::query()->create([
            'nome_razao' => 'PF Um',
            'cpf_cnpj' => $this->uniqueCpfDigits(),
            'tipo_pessoa' => 'PF',
        ]);

        $inscrito = $this->createCursoInscrito([
            'pessoa_id' => $pessoaParticipante->id,
            'agenda_curso_id' => $agenda->id,
            'empresa_id' => $empresa->id,
            'valor' => 100,
            'lancamento_financeiro_id' => $lancamento->id,
        ]);

        $response = $this->actingAs($user)->post(route('cancela-inscricao', ['inscrito' => $inscrito->uid]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('lancamentos_financeiros', ['id' => $lancamento->id]);
        $this->assertDatabaseMissing('curso_inscritos', ['id' => $inscrito->id]);
    }

    public function test_cancela_um_de_varios_inscritos_pj_atualiza_valor_do_lancamento(): void
    {
        $agenda = $this->createAgendaCurso();
        $user = $this->createAuthenticatedUser();

        $empresa = Pessoa::query()->create([
            'nome_razao' => 'Empresa PJ',
            'cpf_cnpj' => $this->uniqueCnpjDigits(),
            'tipo_pessoa' => 'PJ',
        ]);

        $lancamento = $this->createLancamentoParaEmpresaCurso($empresa, $agenda, 180);

        $pessoaA = Pessoa::query()->create([
            'nome_razao' => 'Participante A',
            'cpf_cnpj' => $this->uniqueCpfDigits(),
            'tipo_pessoa' => 'PF',
        ]);
        $pessoaB = Pessoa::query()->create([
            'nome_razao' => 'Participante B',
            'cpf_cnpj' => $this->uniqueCpfDigits(),
            'tipo_pessoa' => 'PF',
        ]);

        $inscritoA = $this->createCursoInscrito([
            'pessoa_id' => $pessoaA->id,
            'agenda_curso_id' => $agenda->id,
            'empresa_id' => $empresa->id,
            'valor' => 100,
            'nome' => 'Participante A',
            'lancamento_financeiro_id' => $lancamento->id,
        ]);

        $this->createCursoInscrito([
            'pessoa_id' => $pessoaB->id,
            'agenda_curso_id' => $agenda->id,
            'empresa_id' => $empresa->id,
            'valor' => 80,
            'nome' => 'Participante B',
            'lancamento_financeiro_id' => $lancamento->id,
        ]);

        $response = $this->actingAs($user)->post(route('cancela-inscricao', ['inscrito' => $inscritoA->uid]));

        $response->assertRedirect();
        $this->assertDatabaseHas('lancamentos_financeiros', [
            'id' => $lancamento->id,
        ]);

        $lancamento->refresh();
        $this->assertSame(80.0, (float) $lancamento->valor);
        $this->assertStringContainsString('80.00', (string) $lancamento->observacoes);
        $this->assertStringNotContainsString('100.00', (string) $lancamento->observacoes);

        if (Schema::hasColumn('curso_inscritos', 'nome')) {
            $this->assertStringContainsString('Participante B', (string) $lancamento->observacoes);
            $this->assertStringNotContainsString('Participante A', (string) $lancamento->observacoes);
        }
    }

    public function test_cancela_inscrito_pf_remove_lancamento_financeiro(): void
    {
        $agenda = $this->createAgendaCurso();
        $user = $this->createAuthenticatedUser();

        $pessoa = Pessoa::query()->create([
            'nome_razao' => 'PF Inscrito',
            'cpf_cnpj' => $this->uniqueCpfDigits(),
            'tipo_pessoa' => 'PF',
        ]);

        $lancamento = LancamentoFinanceiro::query()->create([
            'tipo_lancamento' => 'CREDITO',
            'status' => 'PROVISIONADO',
            'pessoa_id' => $pessoa->id,
            'agenda_curso_id' => $agenda->id,
            'valor' => 100,
            'historico' => 'Inscrição PF teste',
        ]);

        $inscrito = $this->createCursoInscrito([
            'pessoa_id' => $pessoa->id,
            'agenda_curso_id' => $agenda->id,
            'empresa_id' => null,
            'valor' => 100,
            'lancamento_financeiro_id' => $lancamento->id,
            'nome' => 'PF Inscrito',
            'email' => 'pf@example.com',
        ]);

        $response = $this->actingAs($user)->post(route('cancela-inscricao', ['inscrito' => $inscrito->uid]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('lancamentos_financeiros', ['id' => $lancamento->id]);
    }
}

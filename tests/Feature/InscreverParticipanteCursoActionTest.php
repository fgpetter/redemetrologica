<?php

namespace Tests\Feature;

use App\Actions\Financeiro\GerarLancamentoCursoAction;
use App\Actions\SalvaInscritoAction;
use App\Mail\ConfirmacaoInscricaoCursoNotification;
use App\Models\AgendaCursos;
use App\Models\Curso;
use App\Models\CursoInscrito;
use App\Models\Instrutor;
use App\Models\Pessoa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class InscreverParticipanteCursoActionTest extends TestCase
{
    use RefreshDatabase;

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
            'descricao' => 'Curso teste ação',
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
            'investimento' => 150,
            'investimento_associado' => 120,
        ]);
    }

    private function createUserWithPessoa(): User
    {
        $pessoa = Pessoa::query()->create([
            'nome_razao' => 'Usuário Logado',
            'cpf_cnpj' => $this->uniqueCpfDigits(),
            'tipo_pessoa' => 'PF',
        ]);

        $user = User::query()->create([
            'name' => 'Usuário Teste',
            'email' => 'user-'.Str::random(8).'@example.com',
            'password' => bcrypt('password'),
            'temporary_password' => false,
        ]);

        $pessoa->update(['user_id' => $user->id]);

        return $user->fresh();
    }

    public function test_inscricao_cpf_cria_pessoa_endereco_inscrito_e_lancamento(): void
    {
        Mail::fake();

        $agenda = $this->createAgendaCurso();
        $user = $this->createUserWithPessoa();
        $this->actingAs($user);

        $dados = [
            'agenda_curso_id' => $agenda->id,
            'nome' => 'Participante CPF',
            'email' => 'cpf@example.com',
            'telefone' => '(11) 99999-8888',
            'tipo_inscricao' => 'cpf',
            'cpf' => $this->uniqueCpfDigits(),
            'cep' => '01001-000',
            'uf' => 'SP',
            'cidade' => 'São Paulo',
            'bairro' => 'Centro',
            'endereco' => 'Rua Teste, 123',
            'complemento' => 'Apto 1',
            'valor' => 150.00,
        ];

        $inscrito = app(SalvaInscritoAction::class)->criar($agenda, $dados);

        $this->assertInstanceOf(CursoInscrito::class, $inscrito);
        $this->assertDatabaseHas('curso_inscritos', [
            'id' => $inscrito->id,
            'agenda_curso_id' => $agenda->id,
            'email' => 'cpf@example.com',
        ]);

        // Verifica criação de Pessoa PF
        $this->assertDatabaseHas('pessoas', [
            'tipo_pessoa' => 'PF',
            'email' => 'cpf@example.com',
        ]);

        // Verifica criação de Endereco
        $this->assertDatabaseHas('enderecos', [
            'cep' => '01001000',
            'uf' => 'SP',
        ]);

        // Verifica criação de LancamentoFinanceiro
        $this->assertDatabaseHas('lancamentos_financeiros', [
            'agenda_curso_id' => $agenda->id,
            'tipo_lancamento' => 'CREDITO',
            'status' => 'PROVISIONADO',
        ]);

        $this->assertNotNull($inscrito->lancamento_financeiro_id);

        // Verifica que e-mail foi enfileirado
        Mail::assertQueued(ConfirmacaoInscricaoCursoNotification::class);
    }

    public function test_inscricao_cnpj_cria_inscrito_com_empresa_id(): void
    {
        Mail::fake();

        $agenda = $this->createAgendaCurso();
        $user = $this->createUserWithPessoa();
        $this->actingAs($user);

        $empresa = Pessoa::query()->create([
            'nome_razao' => 'Empresa Contratante',
            'cpf_cnpj' => $this->uniqueCnpjDigits(),
            'tipo_pessoa' => 'PJ',
        ]);

        $dados = [
            'agenda_curso_id' => $agenda->id,
            'nome' => 'Participante CNPJ',
            'email' => 'cnpj@example.com',
            'telefone' => '(11) 8888-7777',
            'tipo_inscricao' => 'cnpj',
            'empresa_id' => $empresa->id,
            'cpf' => null,
            'cep' => null,
            'uf' => null,
            'cidade' => null,
            'bairro' => null,
            'endereco' => null,
            'complemento' => null,
            'valor' => null,
        ];

        $inscrito = app(SalvaInscritoAction::class)->criar($agenda, $dados);

        $this->assertInstanceOf(CursoInscrito::class, $inscrito);
        $this->assertEquals($empresa->id, $inscrito->empresa_id);

        // Verifica que NÃO criou nova Pessoa PF
        $this->assertDatabaseMissing('pessoas', [
            'tipo_pessoa' => 'PF',
            'email' => 'cnpj@example.com',
        ]);

        // Verifica criação de LancamentoFinanceiro vinculado à empresa
        $this->assertDatabaseHas('lancamentos_financeiros', [
            'pessoa_id' => $empresa->id,
            'agenda_curso_id' => $agenda->id,
        ]);

        Mail::assertQueued(ConfirmacaoInscricaoCursoNotification::class);
    }

    public function test_email_vazio_cria_inscrito_mas_nao_envia_email(): void
    {
        Mail::fake();

        $agenda = $this->createAgendaCurso();
        $user = $this->createUserWithPessoa();
        $this->actingAs($user);

        $dados = [
            'agenda_curso_id' => $agenda->id,
            'nome' => 'Sem Email',
            'email' => '',
            'telefone' => null,
            'tipo_inscricao' => 'cpf',
            'cpf' => $this->uniqueCpfDigits(),
            'cep' => '01001-000',
            'uf' => 'SP',
            'cidade' => 'São Paulo',
            'bairro' => 'Centro',
            'endereco' => 'Rua Teste, 123',
            'complemento' => null,
            'valor' => 150.00,
        ];

        $inscrito = app(SalvaInscritoAction::class)->criar($agenda, $dados);

        // Inscrição deve ser criada mesmo sem e-mail
        $this->assertInstanceOf(CursoInscrito::class, $inscrito);
        $this->assertDatabaseHas('curso_inscritos', [
            'id' => $inscrito->id,
            'nome' => 'Sem Email',
        ]);

        // Nenhum e-mail deve ser enfileirado
        Mail::assertNothingQueued();
    }

    public function test_rollback_em_falha_nao_persiste_dados(): void
    {
        Mail::fake();

        $agenda = $this->createAgendaCurso();
        $user = $this->createUserWithPessoa();
        $this->actingAs($user);

        // Mock GerarLancamentoCursoAction para lançar exceção e forçar rollback
        $this->mock(GerarLancamentoCursoAction::class, function ($mock) {
            $mock->shouldReceive('execute')->andThrow(new RuntimeException('Falha simulada'));
        });

        $dados = [
            'agenda_curso_id' => $agenda->id,
            'nome' => 'Participante Rollback',
            'email' => 'rollback@example.com',
            'telefone' => null,
            'tipo_inscricao' => 'cpf',
            'cpf' => $this->uniqueCpfDigits(),
            'cep' => '01001-000',
            'uf' => 'SP',
            'cidade' => 'São Paulo',
            'bairro' => 'Centro',
            'endereco' => 'Rua Teste, 123',
            'complemento' => null,
            'valor' => 200.00,
        ];

        try {
            app(SalvaInscritoAction::class)->criar($agenda, $dados);
        } catch (RuntimeException $e) {
            // esperado
        }

        // Verifica que nada foi persistido (rollback da transaction)
        $this->assertDatabaseMissing('curso_inscritos', [
            'nome' => 'Participante Rollback',
        ]);

        $this->assertDatabaseMissing('pessoas', [
            'email' => 'rollback@example.com',
        ]);

        $this->assertDatabaseMissing('lancamentos_financeiros', [
            'agenda_curso_id' => $agenda->id,
        ]);

        Mail::assertNothingQueued();
    }
}

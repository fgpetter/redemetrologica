<?php

namespace Tests\Feature;

use App\Actions\SalvaInscritoAction;
use App\Models\AgendaCursos;
use App\Models\Curso;
use App\Models\CursoInscrito;
use App\Models\Endereco;
use App\Models\Instrutor;
use App\Models\LancamentoFinanceiro;
use App\Models\Pessoa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AtualizarInscritoCursoActionTest extends TestCase
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
            'descricao' => 'Curso teste atualização',
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
            'investimento' => 200,
            'investimento_associado' => 180,
        ]);
    }

    private function createInscritoPF(AgendaCursos $agenda): CursoInscrito
    {
        $pessoa = Pessoa::query()->create([
            'nome_razao' => 'Participante PF',
            'cpf_cnpj' => $this->uniqueCpfDigits(),
            'tipo_pessoa' => 'PF',
            'email' => 'pf@example.com',
            'telefone' => '11988887777',
        ]);

        Endereco::query()->create([
            'pessoa_id' => $pessoa->id,
            'cep' => '02020-000',
            'uf' => 'RJ',
            'cidade' => 'Rio de Janeiro',
            'bairro' => 'Copacabana',
            'endereco' => 'Av Teste, 456',
            'complemento' => null,
        ]);

        $lancamento = LancamentoFinanceiro::query()->create([
            'pessoa_id' => $pessoa->id,
            'agenda_curso_id' => $agenda->id,
            'tipo_lancamento' => 'CREDITO',
            'status' => 'PROVISIONADO',
            'valor' => 200,
            'historico' => 'Inscrição PF teste',
            'observacoes' => 'Participante PF | R$ 200,00',
        ]);

        return CursoInscrito::query()->create([
            'pessoa_id' => $pessoa->id,
            'agenda_curso_id' => $agenda->id,
            'empresa_id' => null,
            'nome' => 'Participante PF',
            'email' => 'pf@example.com',
            'telefone' => '11988887777',
            'valor' => 200,
            'data_inscricao' => now(),
            'lancamento_financeiro_id' => $lancamento->id,
        ]);
    }

    private function createInscritoPJ(AgendaCursos $agenda): CursoInscrito
    {
        $empresa = Pessoa::query()->create([
            'nome_razao' => 'Empresa PJ Atualização',
            'cpf_cnpj' => $this->uniqueCnpjDigits(),
            'tipo_pessoa' => 'PJ',
        ]);

        $pessoaParticipante = Pessoa::query()->create([
            'nome_razao' => 'Participante PJ',
            'cpf_cnpj' => $this->uniqueCpfDigits(),
            'tipo_pessoa' => 'PF',
        ]);

        $lancamento = LancamentoFinanceiro::query()->create([
            'pessoa_id' => $empresa->id,
            'agenda_curso_id' => $agenda->id,
            'tipo_lancamento' => 'CREDITO',
            'status' => 'PROVISIONADO',
            'valor' => 180,
            'historico' => 'Inscrição PJ teste',
            'observacoes' => 'Participante PJ | R$ 180,00',
        ]);

        return CursoInscrito::query()->create([
            'pessoa_id' => $pessoaParticipante->id,
            'agenda_curso_id' => $agenda->id,
            'empresa_id' => $empresa->id,
            'nome' => 'Participante PJ',
            'email' => 'pj@example.com',
            'telefone' => '11977776666',
            'valor' => 180,
            'data_inscricao' => now(),
            'lancamento_financeiro_id' => $lancamento->id,
        ]);
    }

    public function test_atualiza_inscrito_pf_atualiza_pessoa_endereco_e_inscrito(): void
    {
        $agenda = $this->createAgendaCurso();
        $inscrito = $this->createInscritoPF($agenda);

        $dados = [
            'nome' => 'Nome Atualizado',
            'email' => 'atualizado@example.com',
            'telefone' => '11912345678',
            'cpf' => $this->uniqueCpfDigits(),
            'cep' => '03030-000',
            'uf' => 'MG',
            'cidade' => 'Belo Horizonte',
            'bairro' => 'Savassi',
            'endereco' => 'Rua Nova, 789',
            'complemento' => 'Sala 2',
            'valor' => 250.00,
        ];

        app(SalvaInscritoAction::class)->atualizar($inscrito, $dados);

        $inscrito->refresh();

        // Verifica atualização do inscrito
        $this->assertEquals('Nome Atualizado', $inscrito->nome);
        $this->assertEquals('atualizado@example.com', $inscrito->email);
        $this->assertEquals(250.00, (float) $inscrito->valor);

        // Verifica atualização da Pessoa
        $this->assertDatabaseHas('pessoas', [
            'id' => $inscrito->pessoa_id,
            'nome_razao' => 'Nome Atualizado',
            'email' => 'atualizado@example.com',
        ]);

        // Verifica atualização do Endereco
        $this->assertDatabaseHas('enderecos', [
            'pessoa_id' => $inscrito->pessoa_id,
            'cep' => '03030000',
            'uf' => 'MG',
            'cidade' => 'Belo Horizonte',
        ]);

        // Verifica que o lançamento foi atualizado
        $this->assertDatabaseHas('lancamentos_financeiros', [
            'id' => $inscrito->lancamento_financeiro_id,
            'valor' => 250.00,
        ]);
    }

    public function test_atualiza_inscrito_pj_nao_altera_pessoa_nem_endereco(): void
    {
        $agenda = $this->createAgendaCurso();
        $inscrito = $this->createInscritoPJ($agenda);

        $dados = [
            'nome' => 'PJ Nome Novo',
            'email' => 'pjnovo@example.com',
            'telefone' => '11900001111',
            'valor' => 300.00,
        ];

        app(SalvaInscritoAction::class)->atualizar($inscrito, $dados);

        $inscrito->refresh();

        // Verifica atualização do inscrito
        $this->assertEquals('PJ Nome Novo', $inscrito->nome);
        $this->assertEquals('pjnovo@example.com', $inscrito->email);

        // Pessoa original NÃO deve ser alterada (é PJ, não PF)
        $this->assertDatabaseMissing('pessoas', [
            'id' => $inscrito->pessoa_id,
            'nome_razao' => 'PJ Nome Novo',
        ]);
    }

    public function test_atualiza_inscrito_recalcula_valor_lancamento(): void
    {
        $agenda = $this->createAgendaCurso();
        $inscrito = $this->createInscritoPF($agenda);

        $valorOriginal = (float) $inscrito->valor;
        $this->assertEquals(200.00, $valorOriginal);

        $dados = [
            'valor' => 350.00,
        ];

        app(SalvaInscritoAction::class)->atualizar($inscrito, $dados);

        $inscrito->refresh();

        // Verifica valor atualizado no inscrito
        $this->assertEquals(350.00, (float) $inscrito->valor);

        // Verifica valor atualizado no lançamento financeiro
        $lancamento = LancamentoFinanceiro::find($inscrito->lancamento_financeiro_id);
        $this->assertNotNull($lancamento);
        $this->assertEquals(350.00, (float) $lancamento->valor);
    }
}

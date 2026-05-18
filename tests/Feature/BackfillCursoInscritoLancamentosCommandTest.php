<?php

namespace Tests\Feature;

use App\Models\AgendaCursos;
use App\Models\Curso;
use App\Models\CursoInscrito;
use App\Models\Instrutor;
use App\Models\LancamentoFinanceiro;
use App\Models\Pessoa;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class BackfillCursoInscritoLancamentosCommandTest extends TestCase
{
    use DatabaseTransactions;

    public function test_comando_backfill_esta_registrado_no_artisan(): void
    {
        Artisan::call('list', ['--raw' => true]);
        $output = Artisan::output();

        $this->assertStringContainsString('curso-inscritos:backfill-lancamentos', $output);
    }

    public function test_vincula_inscrito_orfao_ao_lancamento_existente(): void
    {
        [$agenda, $pessoa] = $this->criarAgendaEPessoa();

        $lancamento = $this->criarLancamento($pessoa, $agenda);

        $inscrito = $this->criarCursoInscrito([
            'pessoa_id' => $pessoa->id,
            'agenda_curso_id' => $agenda->id,
            'empresa_id' => null,
            'lancamento_financeiro_id' => null,
            'valor' => 100,
            'nome' => 'Participante Teste',
        ]);

        Artisan::call('curso-inscritos:backfill-lancamentos');

        $this->assertDatabaseHas('curso_inscritos', [
            'id' => $inscrito->id,
            'lancamento_financeiro_id' => $lancamento->id,
        ]);
    }

    public function test_reagrega_lancamento_provisionado_apos_vincular(): void
    {
        [$agenda, $pessoa] = $this->criarAgendaEPessoa();

        $lancamento = $this->criarLancamento($pessoa, $agenda, ['valor' => 0]);

        $this->criarCursoInscrito([
            'pessoa_id' => $pessoa->id,
            'agenda_curso_id' => $agenda->id,
            'empresa_id' => null,
            'lancamento_financeiro_id' => null,
            'valor' => 150,
            'nome' => 'Participante Reagregado',
        ]);

        Artisan::call('curso-inscritos:backfill-lancamentos');

        $lancamento->refresh();
        $this->assertSame(150.0, (float) $lancamento->valor);
    }

    public function test_nao_reagrega_lancamento_efetivado(): void
    {
        [$agenda, $pessoa] = $this->criarAgendaEPessoa();

        $lancamento = $this->criarLancamento($pessoa, $agenda, ['status' => 'EFETIVADO', 'valor' => 999]);

        $this->criarCursoInscrito([
            'pessoa_id' => $pessoa->id,
            'agenda_curso_id' => $agenda->id,
            'empresa_id' => null,
            'lancamento_financeiro_id' => null,
            'valor' => 50,
            'nome' => 'Participante EFETIVADO',
        ]);

        Artisan::call('curso-inscritos:backfill-lancamentos');

        $lancamento->refresh();
        $this->assertSame(999.0, (float) $lancamento->valor, 'Valor de lançamento EFETIVADO não deve ser alterado.');
    }

    public function test_dry_run_nao_persiste_vinculos(): void
    {
        [$agenda, $pessoa] = $this->criarAgendaEPessoa();

        $this->criarLancamento($pessoa, $agenda);

        $inscrito = $this->criarCursoInscrito([
            'pessoa_id' => $pessoa->id,
            'agenda_curso_id' => $agenda->id,
            'empresa_id' => null,
            'lancamento_financeiro_id' => null,
            'valor' => 100,
            'nome' => 'Participante Dry Run',
        ]);

        Artisan::call('curso-inscritos:backfill-lancamentos', ['--dry-run' => true]);

        $this->assertDatabaseHas('curso_inscritos', [
            'id' => $inscrito->id,
            'lancamento_financeiro_id' => null,
        ]);
    }

    public function test_nao_deleta_inscritos_duplicados_apenas_vincula_canonico(): void
    {
        [$agenda, $pessoa] = $this->criarAgendaEPessoa();

        $lancamento = $this->criarLancamento($pessoa, $agenda);

        // Três inscritos com a mesma chave (pessoa_id, empresa_id, agenda_curso_id)
        $inscrito1 = $this->criarCursoInscrito([
            'pessoa_id' => $pessoa->id,
            'agenda_curso_id' => $agenda->id,
            'empresa_id' => null,
            'lancamento_financeiro_id' => null,
            'valor' => 100,
            'nome' => 'Participante Dup',
        ]);
        $inscrito2 = $this->criarCursoInscrito([
            'pessoa_id' => $pessoa->id,
            'agenda_curso_id' => $agenda->id,
            'empresa_id' => null,
            'lancamento_financeiro_id' => null,
            'valor' => 100,
            'nome' => 'Participante Dup',
        ]);
        $inscrito3 = $this->criarCursoInscrito([
            'pessoa_id' => $pessoa->id,
            'agenda_curso_id' => $agenda->id,
            'empresa_id' => null,
            'lancamento_financeiro_id' => null,
            'valor' => 100,
            'nome' => 'Participante Dup',
        ]);

        Artisan::call('curso-inscritos:backfill-lancamentos');

        // Todos os registros devem continuar existindo (sem exclusão automática)
        $this->assertDatabaseHas('curso_inscritos', ['id' => $inscrito1->id]);
        $this->assertDatabaseHas('curso_inscritos', ['id' => $inscrito2->id]);
        $this->assertDatabaseHas('curso_inscritos', ['id' => $inscrito3->id]);

        // Apenas o canônico (menor id) deve ter sido vinculado
        $this->assertDatabaseHas('curso_inscritos', [
            'id' => $inscrito1->id,
            'lancamento_financeiro_id' => $lancamento->id,
        ]);
        $this->assertDatabaseHas('curso_inscritos', ['id' => $inscrito2->id, 'lancamento_financeiro_id' => null]);
        $this->assertDatabaseHas('curso_inscritos', ['id' => $inscrito3->id, 'lancamento_financeiro_id' => null]);
    }

    public function test_gera_arquivo_de_log_com_orfao_sem_lancamento(): void
    {
        [$agenda, $pessoa] = $this->criarAgendaEPessoa();

        $this->criarCursoInscrito([
            'pessoa_id' => $pessoa->id,
            'agenda_curso_id' => $agenda->id,
            'empresa_id' => null,
            'lancamento_financeiro_id' => null,
            'valor' => 100,
            'nome' => 'Orphan Sem Lancamento',
        ]);

        $logPath = storage_path('logs/inconsistencias-cursos-lancamentos.log');
        if (file_exists($logPath)) {
            unlink($logPath);
        }

        Artisan::call('curso-inscritos:backfill-lancamentos');

        $this->assertFileExists($logPath);
        $conteudo = (string) file_get_contents($logPath);
        $this->assertStringContainsString('INSCRITO_ORFAO_SEM_LANCAMENTO', $conteudo);
        $this->assertStringContainsString('Orphan Sem Lancamento', $conteudo);
    }

    public function test_ignora_inscrito_orfao_de_curso_in_company(): void
    {
        [$agenda, $pessoa] = $this->criarAgendaEPessoa(inCompany: true);

        $this->criarLancamento($pessoa, $agenda);

        $inscrito = $this->criarCursoInscrito([
            'pessoa_id' => $pessoa->id,
            'agenda_curso_id' => $agenda->id,
            'empresa_id' => null,
            'lancamento_financeiro_id' => null,
            'valor' => 100,
            'nome' => 'Inscrito IN-COMPANY',
        ]);

        Artisan::call('curso-inscritos:backfill-lancamentos');

        $this->assertDatabaseHas('curso_inscritos', [
            'id' => $inscrito->id,
            'lancamento_financeiro_id' => null,
        ]);
    }

    public function test_ignora_lancamento_duplicado_de_curso_in_company(): void
    {
        [$agenda, $pessoa] = $this->criarAgendaEPessoa(inCompany: true);

        $this->criarLancamento($pessoa, $agenda);
        $this->criarLancamento($pessoa, $agenda);

        $logPath = storage_path('logs/inconsistencias-cursos-lancamentos.log');
        if (file_exists($logPath)) {
            unlink($logPath);
        }

        Artisan::call('curso-inscritos:backfill-lancamentos');

        $conteudo = file_exists($logPath) ? (string) file_get_contents($logPath) : '';
        $this->assertStringNotContainsString('LANCAMENTOS_DUPLICADOS', $conteudo);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /** @param array<string, mixed> $attributes */
    private function criarLancamento(Pessoa $pessoa, AgendaCursos $agenda, array $attributes = []): LancamentoFinanceiro
    {
        return LancamentoFinanceiro::query()->create(array_merge([
            'pessoa_id' => $pessoa->id,
            'agenda_curso_id' => $agenda->id,
            'historico' => 'Inscrição no curso',
            'tipo_lancamento' => 'CREDITO',
            'valor' => 100,
            'centro_custo_id' => 3,
            'plano_conta_id' => 3,
            'data_emissao' => now()->toDateString(),
            'status' => 'PROVISIONADO',
        ], $attributes));
    }

    /** @return array{AgendaCursos, Pessoa} */
    private function criarAgendaEPessoa(bool $inCompany = false): array
    {
        $curso = Curso::query()->create([
            'descricao' => 'Curso Backfill Teste',
            'tipo_curso' => 'OFICIAL',
        ]);

        $pessoaInstrutor = Pessoa::query()->create([
            'nome_razao' => 'Instrutor Backfill',
            'cpf_cnpj' => str_pad((string) random_int(10000000000, 99999999999), 11, '0', STR_PAD_LEFT),
            'tipo_pessoa' => 'PF',
        ]);

        $instrutor = Instrutor::query()->create([
            'pessoa_id' => $pessoaInstrutor->id,
            'situacao' => true,
        ]);

        $empresaId = null;
        if ($inCompany) {
            $empresaContratante = Pessoa::query()->create([
                'nome_razao' => 'Empresa IN-COMPANY',
                'cpf_cnpj' => str_pad((string) random_int(10000000000000, 99999999999999), 14, '0', STR_PAD_LEFT),
                'tipo_pessoa' => 'PJ',
            ]);
            $empresaId = $empresaContratante->id;
        }

        $agenda = AgendaCursos::query()->create([
            'uid' => (string) Str::uuid(),
            'curso_id' => $curso->id,
            'instrutor_id' => $instrutor->id,
            'empresa_id' => $empresaId,
            'status' => 'AGENDADO',
            'tipo_agendamento' => $inCompany ? 'IN-COMPANY' : 'ONLINE',
            'inscricoes' => 1,
            'investimento' => 100,
            'investimento_associado' => 80,
        ]);

        $pessoa = Pessoa::query()->create([
            'nome_razao' => 'Participante Backfill',
            'cpf_cnpj' => str_pad((string) random_int(10000000000, 99999999999), 11, '0', STR_PAD_LEFT),
            'tipo_pessoa' => 'PF',
        ]);

        return [$agenda, $pessoa];
    }

    /** @param array<string, mixed> $attributes */
    private function criarCursoInscrito(array $attributes): CursoInscrito
    {
        $attributes['data_inscricao'] ??= now();
        $attributes['valor'] ??= 100;

        if (Schema::hasColumn('curso_inscritos', 'email')) {
            $attributes['email'] ??= 'participante@example.com';
        }

        $colunas = array_flip(Schema::getColumnListing('curso_inscritos'));

        return CursoInscrito::query()->create(array_intersect_key($attributes, $colunas));
    }
}

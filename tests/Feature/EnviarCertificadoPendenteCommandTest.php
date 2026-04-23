<?php

namespace Tests\Feature;

use App\Actions\EnviarCertificadoAction;
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

class EnviarCertificadoPendenteCommandTest extends TestCase
{
    use DatabaseTransactions;

    public function test_nao_faz_nada_quando_nao_ha_inscritos_pendentes(): void
    {
        $action = $this->mock(EnviarCertificadoAction::class);
        $action->shouldNotReceive('execute');

        Artisan::call('certificados:enviar-pendente');
    }

    public function test_ignora_inscrito_com_certificado_ja_emitido(): void
    {
        $action = $this->mock(EnviarCertificadoAction::class);
        $action->shouldNotReceive('execute');

        [$agenda, $pessoa] = $this->criarAgendaEPessoa();
        $lancamento = $this->criarLancamento($pessoa, $agenda, ['status' => 'EFETIVADO']);
        $this->criarCursoInscrito([
            'pessoa_id' => $pessoa->id,
            'agenda_curso_id' => $agenda->id,
            'lancamento_financeiro_id' => $lancamento->id,
            'certificado_emitido' => now(),
        ]);

        Artisan::call('certificados:enviar-pendente');
    }

    public function test_ignora_inscrito_com_lancamento_provisionado(): void
    {
        $action = $this->mock(EnviarCertificadoAction::class);
        $action->shouldNotReceive('execute');

        [$agenda, $pessoa] = $this->criarAgendaEPessoa();
        $lancamento = $this->criarLancamento($pessoa, $agenda, ['status' => 'PROVISIONADO']);
        $this->criarCursoInscrito([
            'pessoa_id' => $pessoa->id,
            'agenda_curso_id' => $agenda->id,
            'lancamento_financeiro_id' => $lancamento->id,
            'certificado_emitido' => null,
        ]);

        Artisan::call('certificados:enviar-pendente');
    }

    public function test_executa_action_para_inscrito_elegivel(): void
    {
        [$agenda, $pessoa] = $this->criarAgendaEPessoa();
        $lancamento = $this->criarLancamento($pessoa, $agenda, ['status' => 'EFETIVADO']);
        $inscrito = $this->criarCursoInscrito([
            'pessoa_id' => $pessoa->id,
            'agenda_curso_id' => $agenda->id,
            'lancamento_financeiro_id' => $lancamento->id,
            'certificado_emitido' => null,
        ]);

        $action = $this->mock(EnviarCertificadoAction::class);
        $action->shouldReceive('execute')->once()->with(\Mockery::on(fn ($arg) => $arg->id === $inscrito->id));

        Artisan::call('certificados:enviar-pendente');
    }

    public function test_processa_inscrito_com_data_inscricao_mais_antiga(): void
    {
        [$agenda, $pessoa] = $this->criarAgendaEPessoa();
        $lancamento = $this->criarLancamento($pessoa, $agenda, ['status' => 'EFETIVADO']);

        $inscritoAntigo = $this->criarCursoInscrito([
            'pessoa_id' => $pessoa->id,
            'agenda_curso_id' => $agenda->id,
            'lancamento_financeiro_id' => $lancamento->id,
            'certificado_emitido' => null,
            'data_inscricao' => now()->subDays(10),
        ]);

        $this->criarCursoInscrito([
            'pessoa_id' => $pessoa->id,
            'agenda_curso_id' => $agenda->id,
            'lancamento_financeiro_id' => $lancamento->id,
            'certificado_emitido' => null,
            'data_inscricao' => now()->subDays(1),
        ]);

        $action = $this->mock(EnviarCertificadoAction::class);
        $action->shouldReceive('execute')->once()->with(\Mockery::on(fn ($arg) => $arg->id === $inscritoAntigo->id));

        Artisan::call('certificados:enviar-pendente');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /** @return array{AgendaCursos, Pessoa} */
    private function criarAgendaEPessoa(): array
    {
        $pessoaInstrutor = Pessoa::query()->create([
            'nome_razao' => 'Instrutor Cert Teste',
            'cpf_cnpj' => str_pad((string) random_int(10000000000, 99999999999), 11, '0', STR_PAD_LEFT),
            'tipo_pessoa' => 'PF',
        ]);

        $instrutor = Instrutor::query()->create([
            'pessoa_id' => $pessoaInstrutor->id,
            'situacao' => true,
        ]);

        $agenda = AgendaCursos::query()->create([
            'uid' => (string) Str::uuid(),
            'curso_id' => Curso::query()->create(['descricao' => 'Curso Cert Teste', 'tipo_curso' => 'OFICIAL'])->id,
            'instrutor_id' => $instrutor->id,
            'empresa_id' => null,
            'status' => 'AGENDADO',
            'tipo_agendamento' => 'ONLINE',
            'inscricoes' => 1,
            'investimento' => 100,
            'investimento_associado' => 80,
        ]);

        $pessoa = Pessoa::query()->create([
            'nome_razao' => 'Participante Cert Teste',
            'cpf_cnpj' => str_pad((string) random_int(10000000000, 99999999999), 11, '0', STR_PAD_LEFT),
            'tipo_pessoa' => 'PF',
        ]);

        return [$agenda, $pessoa];
    }

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

    /** @param array<string, mixed> $attributes */
    private function criarCursoInscrito(array $attributes): CursoInscrito
    {
        $attributes['data_inscricao'] ??= now();
        $attributes['email'] ??= 'participante@example.com';
        $attributes['nome'] ??= 'Participante Teste';

        $colunas = array_flip(Schema::getColumnListing('curso_inscritos'));

        return CursoInscrito::query()->create(array_intersect_key($attributes, $colunas));
    }
}

<?php

namespace Tests\Feature\Cursos;

use App\Jobs\EnviarLinkCertificadoJob;
use App\Livewire\PainelCliente\ConfirmInscricaoCurso;
use App\Mail\CertificadoNotification;
use App\Mail\ConfirmacaoInscricaoCursoNotification;
use App\Models\AgendaCursos;
use App\Models\CentroCusto;
use App\Models\Curso;
use App\Models\CursoInscrito;
use App\Models\DadosGeraDoc;
use App\Models\Instrutor;
use App\Models\LancamentoFinanceiro;
use App\Models\Pessoa;
use App\Models\PlanoConta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class InscricaoCursoCpfTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedCentroCustoEPlanoConta();

        Mail::fake();
        Queue::fake();
    }

    public function test_inscricao_cpf_cria_inscrito_e_lancamento_provisionado(): void
    {
        ['agenda' => $agenda, 'user' => $user, 'inscrito' => $inscrito, 'lancamento' => $lancamento] = $this->inscreverViaLivewireCpf();

        $this->assertDatabaseHas('curso_inscritos', [
            'id' => $inscrito->id,
            'pessoa_id' => $user->pessoa->id,
            'agenda_curso_id' => $agenda->id,
            'empresa_id' => null,
            'certificado_emitido' => null,
            'lancamento_financeiro_id' => $lancamento->id,
        ]);

        $this->assertDatabaseHas('lancamentos_financeiros', [
            'id' => $lancamento->id,
            'pessoa_id' => $user->pessoa->id,
            'agenda_curso_id' => $agenda->id,
            'tipo_lancamento' => 'CREDITO',
            'status' => 'PROVISIONADO',
        ]);

        $inscrito->load('lancamentoFinanceiro');
        $this->assertFalse($inscrito->is_pago);

        Mail::assertQueued(ConfirmacaoInscricaoCursoNotification::class);
        $this->assertNull(session('curso'));
    }

    public function test_inscricao_cpf_e_idempotente_com_update_or_create(): void
    {
        $agenda = $this->createAgendaCursoAberto();
        $user = $this->createClienteUserWithPessoa();
        $dados = $this->dadosInscricaoCpf($user);

        $this->actingAs($user);
        session()->put('curso', $agenda);

        Livewire::test(ConfirmInscricaoCurso::class)
            ->set('tipoInscricao', 'CPF')
            ->set('inscricoes', [$dados])
            ->call('salvarInscricoes');

        session()->put('curso', $agenda);

        Livewire::test(ConfirmInscricaoCurso::class)
            ->set('tipoInscricao', 'CPF')
            ->set('inscricoes', [$dados])
            ->call('salvarInscricoes');

        $this->assertSame(1, CursoInscrito::query()
            ->where('agenda_curso_id', $agenda->id)
            ->where('pessoa_id', $user->pessoa->id)
            ->whereNull('empresa_id')
            ->count());

        $this->assertSame(1, LancamentoFinanceiro::query()
            ->where('agenda_curso_id', $agenda->id)
            ->where('pessoa_id', $user->pessoa->id)
            ->count());
    }

    public function test_baixa_pagamento_atualiza_lancamento_para_efetivado_e_is_pago(): void
    {
        ['inscrito' => $inscrito, 'lancamento' => $lancamento] = $this->inscreverViaLivewireCpf();
        $funcionario = $this->createFuncionarioUser();

        $response = $this->actingAs($funcionario)->post(
            route('lancamento-financeiro-update', $lancamento->uid),
            $this->payloadAtualizacaoLancamento($lancamento, now()->toDateString())
        );

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $lancamento->refresh();
        $inscrito->load('lancamentoFinanceiro');

        $this->assertSame('EFETIVADO', $lancamento->status);
        $this->assertTrue($inscrito->is_pago);
        Queue::assertNothingPushed();
    }

    public function test_marcar_agenda_realizado_dispara_certificado_para_inscrito_pago(): void
    {
        ['agenda' => $agenda, 'inscrito' => $inscrito, 'lancamento' => $lancamento] = $this->inscreverViaLivewireCpf();
        $funcionario = $this->createFuncionarioUser();

        $this->actingAs($funcionario)->post(
            route('lancamento-financeiro-update', $lancamento->uid),
            $this->payloadAtualizacaoLancamento($lancamento, now()->toDateString())
        );

        $this->actingAs($funcionario)->post(
            route('agendamento-curso-update', $agenda->uid),
            $this->payloadAgendaRealizado($agenda)
        );

        $inscrito->refresh();

        $this->assertNotNull($inscrito->certificado_emitido);
        $this->assertNotNull($inscrito->certificado_path);
        $this->assertDatabaseHas('dados_gera_doc', [
            'tipo' => 'certificado',
        ]);

        Queue::assertPushed(EnviarLinkCertificadoJob::class, function (EnviarLinkCertificadoJob $job): bool {
            $job->handle();

            return true;
        });

        Mail::assertQueued(CertificadoNotification::class);
    }

    public function test_marcar_agenda_realizado_nao_dispara_certificado_para_inscrito_nao_pago(): void
    {
        ['agenda' => $agenda, 'inscrito' => $inscrito] = $this->inscreverViaLivewireCpf();
        $funcionario = $this->createFuncionarioUser();

        $this->actingAs($funcionario)->post(
            route('agendamento-curso-update', $agenda->uid),
            $this->payloadAgendaRealizado($agenda)
        );

        $inscrito->refresh();

        $this->assertNull($inscrito->certificado_emitido);
        $this->assertSame(0, DadosGeraDoc::query()->where('tipo', 'certificado')->count());
        Queue::assertNothingPushed();
    }

    public function test_marcar_agenda_realizado_nao_reemite_certificado_ja_emitido(): void
    {
        ['agenda' => $agenda, 'inscrito' => $inscrito, 'lancamento' => $lancamento] = $this->inscreverViaLivewireCpf();
        $funcionario = $this->createFuncionarioUser();

        $this->actingAs($funcionario)->post(
            route('lancamento-financeiro-update', $lancamento->uid),
            $this->payloadAtualizacaoLancamento($lancamento, now()->toDateString())
        );

        $inscrito->update([
            'certificado_emitido' => now()->subDay(),
            'certificado_path' => 'public/docs/certificados/certificado-existente.pdf',
        ]);

        $this->actingAs($funcionario)->post(
            route('agendamento-curso-update', $agenda->uid),
            $this->payloadAgendaRealizado($agenda)
        );

        Queue::assertNothingPushed();
    }

    public function test_inscricao_cpf_rejeita_dados_obrigatorios_ausentes(): void
    {
        $agenda = $this->createAgendaCursoAberto();
        $user = $this->createClienteUserWithPessoa();

        $this->actingAs($user);
        session()->put('curso', $agenda);

        Livewire::test(ConfirmInscricaoCurso::class)
            ->set('tipoInscricao', 'CPF')
            ->set('inscricoes', [[
                'id_pessoa' => $user->pessoa->id,
                'nome' => '',
                'email' => '',
                'telefone' => '',
                'cpf_cnpj' => '',
                'responsavel' => 1,
            ]])
            ->call('salvarInscricoes')
            ->assertHasErrors(['inscricoes.0.nome', 'inscricoes.0.email']);

        $this->assertDatabaseCount('curso_inscritos', 0);
        $this->assertDatabaseCount('lancamentos_financeiros', 0);
    }

    /**
     * @return array{agenda: AgendaCursos, user: User, inscrito: CursoInscrito, lancamento: LancamentoFinanceiro}
     */
    private function inscreverViaLivewireCpf(): array
    {
        $agenda = $this->createAgendaCursoAberto();
        $user = $this->createClienteUserWithPessoa();
        $dados = $this->dadosInscricaoCpf($user);

        $this->actingAs($user);
        session()->put('curso', $agenda);

        Livewire::test(ConfirmInscricaoCurso::class)
            ->set('tipoInscricao', 'CPF')
            ->set('inscricoes', [$dados])
            ->call('salvarInscricoes')
            ->assertRedirect('painel');

        $inscrito = CursoInscrito::query()
            ->where('agenda_curso_id', $agenda->id)
            ->where('pessoa_id', $user->pessoa->id)
            ->whereNull('empresa_id')
            ->firstOrFail();

        $lancamento = LancamentoFinanceiro::query()->findOrFail($inscrito->lancamento_financeiro_id);

        return compact('agenda', 'user', 'inscrito', 'lancamento');
    }

    /**
     * @return array<string, mixed>
     */
    private function dadosInscricaoCpf(User $user, ?string $cpf = null): array
    {
        return [
            'id_pessoa' => $user->pessoa->id,
            'nome' => 'Participante CPF Teste',
            'email' => strtolower($user->email),
            'telefone' => '11999998888',
            'cpf_cnpj' => $cpf ?? $this->uniqueCpfDigits(),
            'cep' => '01001-000',
            'endereco' => 'Rua Teste, 123',
            'complemento' => 'Apto 1',
            'bairro' => 'Centro',
            'cidade' => 'São Paulo',
            'uf' => 'SP',
            'responsavel' => 1,
        ];
    }

    private function createAgendaCursoAberto(): AgendaCursos
    {
        $curso = Curso::query()->create([
            'descricao' => 'Curso inscrição CPF teste',
            'tipo_curso' => 'OFICIAL',
            'carga_horaria' => 8,
            'conteudo_programatico' => 'Conteúdo programático de teste',
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
            'data_inicio' => now()->addWeek(),
            'data_fim' => now()->addWeek(),
        ]);
    }

    private function createClienteUserWithPessoa(): User
    {
        $user = User::query()->create([
            'name' => 'Cliente Teste',
            'email' => 'cliente-'.Str::random(8).'@example.com',
            'password' => bcrypt('password'),
            'temporary_password' => false,
        ]);

        $permissionId = DB::table('permissions')->where('permission', 'cliente')->value('id');
        if ($permissionId === null) {
            $permissionId = DB::table('permissions')->insertGetId([
                'permission' => 'cliente',
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
            'nome_razao' => 'Cliente PF',
            'cpf_cnpj' => $this->uniqueCpfDigits(),
            'tipo_pessoa' => 'PF',
            'email' => $user->email,
        ]);

        return $user->fresh('pessoa');
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
     * @return array<string, string>
     */
    private function payloadAtualizacaoLancamento(LancamentoFinanceiro $lancamento, ?string $dataPagamento): array
    {
        return [
            'pessoa_id' => (string) $lancamento->pessoa_id,
            'centro_custo_id' => (string) ($lancamento->centro_custo_id ?? CentroCusto::ID_TREINAMENTO),
            'plano_conta_id' => (string) ($lancamento->plano_conta_id ?? PlanoConta::ID_RECEITA_PRESTACAO_SERVICOS),
            'tipo_lancamento' => 'CREDITO',
            'valor' => number_format((float) $lancamento->valor, 2, ',', '.'),
            'data_emissao' => optional($lancamento->data_emissao)->format('Y-m-d') ?? now()->toDateString(),
            'data_pagamento' => $dataPagamento,
            'historico' => $lancamento->historico,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function payloadAgendaRealizado(AgendaCursos $agenda): array
    {
        return [
            'status' => 'REALIZADO',
            'tipo_agendamento' => $agenda->tipo_agendamento,
            'curso_id' => (string) $agenda->curso_id,
            'instrutor_id' => (string) $agenda->instrutor_id,
            'data_inicio' => $agenda->data_inicio->format('Y-m-d'),
            'data_fim' => $agenda->data_fim->format('Y-m-d'),
            'inscricoes' => (string) $agenda->inscricoes,
            'investimento' => number_format((float) $agenda->investimento, 2, ',', '.'),
            'investimento_associado' => number_format((float) $agenda->investimento_associado, 2, ',', '.'),
        ];
    }

    private function uniqueCpfDigits(): string
    {
        return str_pad((string) random_int(10000000000, 99999999999), 11, '0', STR_PAD_LEFT);
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

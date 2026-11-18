<?php

namespace Tests\Feature\Cursos;

use App\Actions\EnviarMateriaisCursoAction;
use App\Livewire\Cursos\ListParticipantes;
use App\Mail\InformacoesTreinamentoMail;
use App\Models\AgendaCursos;
use App\Models\CentroCusto;
use App\Models\Curso;
use App\Models\CursoInscrito;
use App\Models\Instrutor;
use App\Models\LancamentoFinanceiro;
use App\Models\Pessoa;
use App\Models\PlanoConta;
use App\Models\User;
use Database\Factories\PessoaFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
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

        CursoInscrito::query()->create([
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

        $inscrito = CursoInscrito::query()->first();
        $this->assertNull($inscrito->lancamento_financeiro_id);
        $this->assertNull($inscrito->valor);
    }

    public function test_envia_materiais_apenas_ao_inscrito_selecionado_e_preserva_ordenacao(): void
    {
        Mail::fake();
        $agenda = $this->createAgendaInCompany($this->createEmpresa());
        $agenda->update(['tipo_agendamento' => 'ONLINE', 'status' => 'CONFIRMADO', 'horario' => '08:00', 'link_aula' => 'https://example.com/aula']);
        $agenda->materiaisTreinamento()->create(['arquivo' => 'cursos/apostila.pdf', 'descricao' => 'Apostila']);
        $inscrito = $this->createInscrito($agenda);
        $this->createInscrito($agenda, ['email' => 'outro@example.com']);
        $this->actingAs($this->createFuncionarioUser());

        Livewire::test(ListParticipantes::class, ['agendacurso' => $agenda])
            ->assertSee('Enviar materiais')
            ->assertSee('Enviar Certificado')
            ->assertSee('Baixar Certificado')
            ->set('sortBy', 'nome')
            ->set('sortDirection', 'DESC')
            ->call('enviarMateriais', $inscrito->uid)
            ->assertSet('sortBy', 'nome')
            ->assertSet('sortDirection', 'DESC')
            ->assertDispatched('show-success-alert', message: 'Envio de materiais solicitado com sucesso!');

        Mail::assertQueuedCount(1);
        Mail::assertQueued(InformacoesTreinamentoMail::class, function (InformacoesTreinamentoMail $mail) use ($agenda, $inscrito): bool {
            return $mail->hasTo($inscrito->email)
                && count($mail->to) === 1
                && $mail->dados['nome'] === $inscrito->nome
                && $mail->dados['treinamento'] === $agenda->curso->descricao
                && $mail->dados['data_curso'] === $agenda->data_inicio->toDateString()
                && $mail->dados['horario'] === '08:00'
                && $mail->dados['link'] === 'https://example.com/aula'
                && $mail->dados['materiais'] === [['descricao' => 'Apostila', 'url' => Storage::disk('public')->url('cursos/apostila.pdf')]]
                && $mail->connection === 'database';
        });
    }

    public function test_worker_processa_email_manual_enfileirado(): void
    {
        config(['mail.default' => 'array']);
        $agenda = $this->createAgendaInCompany($this->createEmpresa());
        $agenda->update(['horario' => '08:00', 'link_aula' => 'https://example.com/aula']);
        $agenda->materiaisTreinamento()->create(['arquivo' => 'cursos/apostila.pdf', 'descricao' => 'Apostila']);
        $inscrito = $this->createInscrito($agenda);
        $this->actingAs($this->createFuncionarioUser());

        Livewire::test(ListParticipantes::class, ['agendacurso' => $agenda])
            ->call('enviarMateriais', $inscrito->uid)
            ->assertDispatched('show-success-alert', message: 'Envio de materiais solicitado com sucesso!');

        $this->assertDatabaseCount('jobs', 1);
        $this->artisan('queue:work', ['connection' => 'database', '--once' => true])->assertSuccessful();
        $this->assertDatabaseCount('jobs', 0);
        $this->assertDatabaseCount('failed_jobs', 0);
        $messages = Mail::mailer('array')->getSymfonyTransport()->messages();
        $this->assertCount(1, $messages);
        $email = $messages->first()->getOriginalMessage();
        $this->assertSame($inscrito->email, $email->getTo()[0]->getAddress());
        $this->assertStringContainsString('Apostila', $email->getHtmlBody());
        $this->assertStringContainsString('https://example.com/aula', $email->getHtmlBody());
    }

    public function test_permite_reenvio_manual_apos_envio_automatico(): void
    {
        Mail::fake();
        $agenda = $this->createAgendaInCompany($this->createEmpresa());
        $agenda->update(['informacoes_enviadas_em' => now()]);
        $inscrito = $this->createInscrito($agenda, ['informacoes_enviadas_em' => now()]);
        $this->actingAs($this->createFuncionarioUser());

        Livewire::test(ListParticipantes::class, ['agendacurso' => $agenda])
            ->call('enviarMateriais', $inscrito->uid)
            ->call('enviarMateriais', $inscrito->uid)
            ->assertDispatched('show-success-alert', message: 'Envio de materiais solicitado com sucesso!');

        Mail::assertQueued(InformacoesTreinamentoMail::class, 2);
    }

    public function test_nao_envia_materiais_para_inscrito_de_outra_agenda(): void
    {
        Mail::fake();
        $agenda = $this->createAgendaInCompany($this->createEmpresa());
        $outraAgenda = $this->createAgendaInCompany($this->createEmpresa());
        $inscrito = $this->createInscrito($outraAgenda);
        $this->actingAs($this->createFuncionarioUser());

        Livewire::test(ListParticipantes::class, ['agendacurso' => $agenda])
            ->call('enviarMateriais', $inscrito->uid)
            ->assertNotFound();

        Mail::assertNothingOutgoing();
    }

    public function test_envio_de_materiais_exige_permissao_existente(): void
    {
        Mail::fake();
        $agenda = $this->createAgendaInCompany($this->createEmpresa());
        $inscrito = $this->createInscrito($agenda);
        $this->actingAs(User::factory()->create());

        Livewire::test(ListParticipantes::class, ['agendacurso' => $agenda])
            ->call('enviarMateriais', $inscrito->uid)
            ->assertNotFound();

        Mail::assertNothingOutgoing();
    }

    public function test_informa_falha_ao_solicitar_envio_de_materiais(): void
    {
        Mail::fake();
        $agenda = $this->createAgendaInCompany($this->createEmpresa());
        $inscrito = $this->createInscrito($agenda);
        $this->actingAs($this->createFuncionarioUser());
        $this->mock(EnviarMateriaisCursoAction::class)
            ->shouldReceive('execute')->once()->andThrow(new \RuntimeException('Falha de teste'));

        Livewire::test(ListParticipantes::class, ['agendacurso' => $agenda])
            ->call('enviarMateriais', $inscrito->uid)
            ->assertDispatched('show-error-alert', message: 'Houve um erro ao solicitar o envio dos materiais. Tente novamente.')
            ->assertNotDispatched('show-success-alert');

        Mail::assertNothingOutgoing();
    }

    public function test_email_invalido_exibe_erro_e_registra_validacao(): void
    {
        Mail::fake();
        $agenda = $this->createAgendaInCompany($this->createEmpresa());
        $inscrito = $this->createInscrito($agenda, ['email' => 'invalido']);
        $this->actingAs($this->createFuncionarioUser());
        Log::spy();
        Log::shouldReceive('channel')->with('validation')->andReturnSelf();

        Livewire::test(ListParticipantes::class, ['agendacurso' => $agenda])
            ->call('enviarMateriais', $inscrito->uid)
            ->assertDispatched('show-error-alert', message: 'Não foi possível enviar os materiais: o e-mail do inscrito é inválido ou não foi informado.')
            ->assertNotDispatched('show-success-alert');

        Log::shouldHaveReceived('warning')->once();
        Mail::assertNothingOutgoing();
    }

    private function createInscrito(AgendaCursos $agenda, array $extra = []): CursoInscrito
    {
        return $agenda->inscritos()->create(array_merge([
            'pessoa_id' => PessoaFactory::new()->create()->id,
            'nome' => 'Participante Teste',
            'email' => 'participante@example.com',
            'data_inscricao' => now(),
        ], $extra));
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

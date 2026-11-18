<?php

use App\Actions\EnviarCertificadoAction;
use App\Actions\Financeiro\EditarLancamentosEmLoteAction;
use App\Actions\Interlab\ProcessarInterlabLotePostagemAction;
use App\Actions\Interlab\RastrearItemLotePostagemAction;
use App\Actions\Interlab\ResultadoEtapaLotePostagem;
use App\Enums\InterlabLotePostagemStatus;
use App\Exceptions\CorreiosApiException;
use App\Exceptions\OperacaoImpedidaException;
use App\Integrations\Correios\CorreiosLog;
use App\Jobs\EnviarLinkCertificadoJob;
use App\Jobs\EnviaSenhaAnalistaJob;
use App\Jobs\EnviaSenhaLaboratorioJob;
use App\Jobs\Interlab\ProcessarInterlabLotePostagemJob;
use App\Jobs\Interlab\RastrearItemLotePostagemJob;
use App\Livewire\Interlab\GerarCertificadoButton;
use App\Models\AgendaCursos;
use App\Models\Curso;
use App\Models\CursoInscrito;
use App\Models\DadosGeraDoc;
use App\Models\Instrutor;
use App\Models\LancamentoFinanceiro;
use App\Models\Permission;
use App\Models\User;
use App\Support\Observabilidade\ResumoExecucao;
use Database\Factories\InterlabLotePostagemFactory;
use Database\Factories\InterlabLotePostagemItemFactory;
use Database\Factories\LancamentoFinanceiroFactory;
use Database\Factories\PessoaFactory;
use Illuminate\Database\DatabaseTransactionsManager;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Log\LogManager;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Monolog\Handler\TestHandler;
use Monolog\Logger;

function capturarObservabilidade(string $canal = 'mailsent'): TestHandler
{
    $handler = new TestHandler;
    Log::swap(new class($handler) extends LogManager
    {
        public function __construct(private TestHandler $handler)
        {
            parent::__construct(app());
        }

        public function channel($channel = null)
        {
            return new Illuminate\Log\Logger(new Logger('teste', [$this->handler]));
        }
    });

    return $handler;
}

it('registra um resumo ordenado após commit e nenhum sucesso após rollback', function () {
    $handler = capturarObservabilidade();
    DB::connection()->setTransactionManager(new DatabaseTransactionsManager);
    DB::beginTransaction();
    (new ResumoExecucao('teste.commit'))->executar(function ($resumo) {
        $resumo->etapa('validar', fn () => true);
        $resumo->etapa('persistir', fn () => true);
        $resumo->resultado('enfileirado');
    });
    expect($handler->getRecords())->toBeEmpty();
    DB::commit();
    // RefreshDatabase mantém uma transação externa: os callbacks só devem aparecer no commit externo.
    DB::commit();
    expect($handler->getRecords())->toHaveCount(1);
    $contexto = $handler->getRecords()[0]->context;
    expect(array_column($contexto['etapas'], 'etapa'))->toBe(['processar', 'validar', 'persistir'])
        ->and($contexto['resultado'])->toBe('enfileirado');

    DB::beginTransaction();
    (new ResumoExecucao('teste.rollback'))->executar(fn () => true);
    DB::rollBack();
    expect($handler->getRecords())->toHaveCount(1);
});

it('preserva o fluxo da Action em chamada isolada e com principal', function (bool $comPrincipal) {
    $handler = capturarObservabilidade();
    $principal = $comPrincipal ? new ResumoExecucao('teste.principal') : null;
    $inscrito = new CursoInscrito;
    $inscrito->id = -1;

    expect(fn () => app(EnviarCertificadoAction::class)->execute($inscrito, resumo: $principal))
        ->toThrow(ModelNotFoundException::class);

    $registros = $handler->getRecords();
    expect($registros)->toHaveCount(1)
        ->and($registros[0]->context['fluxo'])->toBe('EnviarCertificadoAction');

    if ($principal !== null) {
        expect($registros[0]->context['execucao_pai_id'])->toBe($principal->correlacao()['execucao_id']);
    } else {
        expect($registros[0]->context)->not->toHaveKey('execucao_pai_id');
    }
})->with([false, true]);

it('correlaciona a falha consumida de um item sem contaminar o sucesso seguinte', function () {
    Context::flush();
    $correlacoes = [];
    Exceptions::reportable(function (Throwable $exception) use (&$correlacoes): void {
        $correlacoes[] = Context::all();
    });

    $principal = new ResumoExecucao('teste.lote', ['agenda_uid' => 'agenda-1']);
    $itens = [];
    $principal->executar(function (ResumoExecucao $principal) use (&$itens): void {
        $falha = $principal->item('teste.item', ['inscrito_id' => 1]);
        $itens[] = $falha;

        try {
            Context::scope(fn () => throw new RuntimeException('falha sintetica do item A'), $falha->correlacao());
        } catch (Throwable $exception) {
            $falha->reportar($exception);
            $principal->incrementar('falhas');
        } finally {
            $falha->encerrar();
        }

        $sucesso = $principal->item('teste.item', ['inscrito_id' => 2]);
        $itens[] = $sucesso;
        $sucesso->resultado('enfileirado');
        $sucesso->incrementar('enfileirados');
        $sucesso->encerrar();
        $principal->incrementar('enfileirados');
        $principal->resultado('falha_definitiva', 'erro_tecnico');
    });

    expect($correlacoes)->toHaveCount(1)
        ->and($correlacoes[0]['inscrito_id'])->toBe(1)
        ->and($correlacoes[0]['fluxo'])->toBe('teste.item')
        ->and($correlacoes[0]['execucao_id'])->toBe($itens[0]->correlacao()['execucao_id'])
        ->and($correlacoes[0]['execucao_pai_id'])->toBe($principal->correlacao()['execucao_id'])
        ->and($itens[1]->correlacao()['inscrito_id'])->toBe(2)
        ->and($itens[1]->correlacao())->not->toHaveKey('documento_id')
        ->and(Context::all())->toBe([]);

    Context::flush();
});

it('emite resumo principal e um resumo independente por operação de item', function () {
    $handler = capturarObservabilidade();
    DB::connection()->setTransactionManager(new DatabaseTransactionsManager);
    DB::beginTransaction();

    $principal = new ResumoExecucao('teste.lote', ['agenda_uid' => 'agenda-1']);
    $principal->executar(function (ResumoExecucao $principal): void {
        $primeiro = $principal->item('teste.item', ['inscrito_id' => 1]);
        $primeiro->resultado('enfileirado');
        $primeiro->incrementar('enfileirados');
        $primeiro->encerrar();

        $segundo = $principal->item('teste.item', ['inscrito_id' => 2]);
        $segundo->resultado('enfileirado');
        $segundo->incrementar('enfileirados');
        $segundo->encerrar();

        $principal->incrementar('enfileirados', 2);
        $principal->resultado('enfileirado');
    });

    DB::commit();
    DB::commit();

    $registros = $handler->getRecords();
    $principais = array_values(array_filter($registros, fn ($registro) => ! isset($registro->context['execucao_pai_id'])));
    $itens = array_values(array_filter($registros, fn ($registro) => isset($registro->context['execucao_pai_id'])));

    expect($principais)->toHaveCount(1)
        ->and($itens)->toHaveCount(2)
        ->and($itens[0]->context['inscrito_id'])->toBe(1)
        ->and($itens[1]->context['inscrito_id'])->toBe(2)
        ->and($itens[0]->context['execucao_id'])->not->toBe($itens[1]->context['execucao_id'])
        ->and($itens[0]->context['execucao_pai_id'])->toBe($itens[1]->context['execucao_pai_id'])
        ->and($itens[0]->context['execucao_pai_id'])->toBe($principal->correlacao()['execucao_id'])
        ->and($principais[0]->context)->not->toHaveKey('inscrito_id')
        ->and($principais[0]->context['metricas']['enfileirados'])->toBe(2);
});

it('não anuncia item enfileirado quando o commit externo é revertido', function () {
    $handler = capturarObservabilidade();
    DB::connection()->setTransactionManager(new DatabaseTransactionsManager);
    DB::beginTransaction();

    $principal = new ResumoExecucao('teste.lote');
    $principal->executar(function (ResumoExecucao $principal): void {
        $item = $principal->item('teste.item', ['inscrito_id' => 1]);
        $item->resultado('enfileirado');
        $item->incrementar('enfileirados');
        $item->encerrar();
        $principal->incrementar('enfileirados');
        $principal->resultado('enfileirado');
    });

    expect($handler->getRecords())->toBeEmpty();
    DB::rollBack();
    expect($handler->getRecords())->toBeEmpty();
});

it('processa certificados do mesmo lote com correlação por inscrito após falha consumida', function () {
    Queue::fake();
    Context::flush();

    $usuario = User::factory()->create();
    $permissao = Permission::withoutEvents(fn () => Permission::query()->firstOrCreate(['permission' => 'funcionario']));
    $usuario->permissions()->syncWithoutDetaching([$permissao->id]);
    $this->actingAs($usuario);

    $curso = Curso::query()->create([
        'descricao' => 'Curso de certificado em lote',
        'tipo_curso' => 'OFICIAL',
        'carga_horaria' => 8,
        'conteudo_programatico' => 'Conteúdo',
    ]);
    $instrutor = Instrutor::query()->create([
        'pessoa_id' => PessoaFactory::new()->create()->id,
        'situacao' => true,
    ]);
    $agenda = AgendaCursos::query()->create([
        'curso_id' => $curso->id,
        'instrutor_id' => $instrutor->id,
        'status' => 'CONFIRMADO',
        'tipo_agendamento' => 'ONLINE',
        'inscricoes' => 2,
        'investimento' => 100,
        'investimento_associado' => 80,
        'data_inicio' => '2026-11-15',
        'data_fim' => '2026-11-15',
        'horario' => '08:00',
    ]);

    $criarInscrito = function (string $email) use ($agenda): CursoInscrito {
        $pessoa = PessoaFactory::new()->create();
        $lancamento = LancamentoFinanceiroFactory::new()->create([
            'pessoa_id' => $pessoa->id,
            'agenda_curso_id' => $agenda->id,
            'status' => 'EFETIVADO',
        ]);

        return CursoInscrito::query()->create([
            'agenda_curso_id' => $agenda->id,
            'pessoa_id' => $pessoa->id,
            'nome' => 'Participante',
            'email' => $email,
            'lancamento_financeiro_id' => $lancamento->id,
            'valor' => 100,
            'data_inscricao' => now(),
        ]);
    };

    $falha = $criarInscrito('falha@example.com');
    $sucesso = $criarInscrito('sucesso@example.com');

    $this->app->instance(EnviarCertificadoAction::class, new class($falha->id) extends EnviarCertificadoAction
    {
        public function __construct(private int $falhaId) {}

        public function execute(CursoInscrito $inscrito, int $delay = 0, ?ResumoExecucao $resumo = null): DadosGeraDoc
        {
            if ($inscrito->id === $this->falhaId) {
                throw new RuntimeException('Falha simulada do primeiro inscrito');
            }

            return parent::execute($inscrito, $delay, $resumo);
        }
    });

    $correlacoes = [];
    Exceptions::reportable(function (Throwable $exception) use (&$correlacoes): void {
        $correlacoes[] = Context::all();
    });

    $this->post(route('agendamento-curso-update', $agenda->uid), [
        'status' => 'REALIZADO',
        'tipo_agendamento' => 'ONLINE',
        'curso_id' => $curso->id,
        'instrutor_id' => $instrutor->id,
        'data_inicio' => '2026-11-15',
        'data_fim' => '2026-11-15',
        'horario' => '08:00',
    ])->assertRedirect(route('agendamento-curso-index'));

    expect($correlacoes)->toHaveCount(1)
        ->and($correlacoes[0]['inscrito_id'])->toBe($falha->id)
        ->and($correlacoes[0]['inscrito_id'])->not->toBe($sucesso->id)
        ->and($correlacoes[0]['execucao_pai_id'])->not->toBeNull()
        ->and($correlacoes[0]['execucao_id'])->not->toBe($correlacoes[0]['execucao_pai_id'])
        ->and($falha->fresh()->certificado_emitido)->toBeNull()
        ->and($sucesso->fresh()->certificado_emitido)->not->toBeNull();

    Queue::assertPushed(EnviarLinkCertificadoJob::class, 1);
    Context::flush();
});

it('registra falha propagada sem reportar novamente nem expor mensagem', function () {
    $handler = capturarObservabilidade();
    $exception = new RuntimeException('privado@example.com token=segredo');
    try {
        (new ResumoExecucao('teste.falha'))->executar(fn ($resumo) => $resumo->etapa('enviar', fn () => throw $exception), job: true);
    } catch (RuntimeException $capturada) {
        expect($capturada)->toBe($exception);
    }
    expect($handler->getRecords())->toHaveCount(1);
    $registro = $handler->getRecords()[0];
    expect($registro->level->getName())->toBe('WARNING')
        ->and($registro->context['resultado'])->toBe('falha_execucao')
        ->and(json_encode($registro->context))->not->toContain('segredo', 'privado@example.com');
});

it('mantém jobs consecutivos isolados e documento ausente como impedimento sem envio', function () {
    $handler = capturarObservabilidade();
    Mail::fake();
    (new EnviaSenhaAnalistaJob(999999, ['privado@example.com'], 41))->handle();
    (new EnviaSenhaLaboratorioJob(999998, ['outro@example.com'], 42))->handle();
    $registros = $handler->getRecords();
    expect($registros)->toHaveCount(2)
        ->and($registros[0]->context['motivo'])->toBe('documento_ausente')
        ->and($registros[1]->context['motivo'])->toBe('documento_ausente')
        ->and($registros[0]->context['execucao_id'])->not->toBe($registros[1]->context['execucao_id'])
        ->and($registros[1]->context)->not->toHaveKey('analista_id')
        ->and(json_encode($registros[0]->context))->not->toContain('privado@example.com');
    Mail::assertNothingOutgoing();
});

it('remove corpos e dados pessoais também do debug local dos Correios', function () {
    $handler = capturarObservabilidade('correios');
    Http::fake(['*' => Http::response(['email' => 'privado@example.com', 'dados' => 'PDFSECRETO'], 200)]);
    CorreiosLog::info('http.detalhe', ['token_id' => 'segredo-123', 'body' => 'PDFSECRETO', 'email' => 'privado@example.com', 'nome' => 'Nome Privado', 'status_http' => 200]);
    $response = Http::withRequestMiddleware(function ($request) {
        CorreiosLog::info('http.detalhe', ['body' => 'PDFSECRETO', 'email' => 'privado@example.com', 'nome' => 'Nome Privado', 'status_http' => 200]);

        return $request;
    })->get('https://example.com/segredo');
    CorreiosLog::response('rastrear', $response);
    $conteudo = json_encode(array_map(fn ($registro) => ['message' => $registro->message, 'context' => $registro->context], $handler->getRecords()));
    expect($conteudo)->not->toContain('PDFSECRETO', 'privado@example.com', 'Nome Privado', 'https://example.com/segredo', 'segredo-123')
        ->and($conteudo)->toContain('status_http');
});

it('validação do certificado Interlab usa Form Object e SWAL sem incidente técnico', function () {
    Exceptions::fake();
    Livewire::test(GerarCertificadoButton::class, ['participanteId' => 999999])
        ->set('email', 'invalido')
        ->call('enviarCertificado')
        ->assertDispatched('show-error-alert', message: 'Verifique o e-mail informado para o certificado.');
    Exceptions::assertNothingReported();
});

it('recusa financeira conhecida tem tipo e motivo sem classificar pela mensagem', function () {
    $credito = LancamentoFinanceiro::factory()->create(['tipo_lancamento' => 'CREDITO']);
    $debito = LancamentoFinanceiro::factory()->create(['tipo_lancamento' => 'DEBITO']);
    try {
        app(EditarLancamentosEmLoteAction::class)->execute([$credito->uid, $debito->uid], ['nota_fiscal' => 'nova']);
        $this->fail('Esperava recusa de negócio.');
    } catch (OperacaoImpedidaException $exception) {
        expect($exception->motivo)->toBe('tipos_distintos');
    }
    expect($credito->fresh()->nota_fiscal)->not->toBe('nova');
});

it('preserva releases e distingue continuação de retentativa e falha definitiva no resumo', function (string $estado, string $esperado, string $nivel) {
    $lote = InterlabLotePostagemFactory::new()->create([
        'status' => InterlabLotePostagemStatus::GerandoPrePostagens,
    ]);
    $handler = capturarObservabilidade();
    $resultado = match ($estado) {
        'continuidade' => ResultadoEtapaLotePostagem::retentar(5, 'continuidade'),
        'falha' => ResultadoEtapaLotePostagem::falhou(),
        default => ResultadoEtapaLotePostagem::retentar(5),
    };
    $action = Mockery::mock(ProcessarInterlabLotePostagemAction::class);
    $action->shouldReceive('execute')->once()->andReturn($resultado);
    $job = (new ProcessarInterlabLotePostagemJob($lote))->withFakeQueueInteractions();
    $job->handle($action);
    $registros = array_values(array_filter($handler->getRecords(), fn ($registro) => $registro->message === 'fluxo.execucao'));
    expect($registros)->toHaveCount(1)
        ->and($registros[0]->level->getName())->toBe($nivel)
        ->and($registros[0]->context['resultado'])->toBe($esperado);
    if ($estado === 'falha') {
        $job->assertNotReleased();
    } else {
        $job->assertReleased(5);
    }
})->with([
    ['continuidade', 'encaminhado', 'INFO'],
    ['retentativa', 'retentativa', 'WARNING'],
    ['falha', 'falha_definitiva', 'ERROR'],
]);

it('rastreio com erro definitivo retorna normalmente mas registra falha funcional', function () {
    $item = InterlabLotePostagemItemFactory::new()->create();
    $handler = capturarObservabilidade();
    $action = Mockery::mock(RastrearItemLotePostagemAction::class);
    $action->shouldReceive('execute')->once()->andThrow(new CorreiosApiException('Recusa', 400, ['body' => 'conteudo privado'], '/rota'));
    $job = (new RastrearItemLotePostagemJob($item))->withFakeQueueInteractions();
    $job->handle($action);
    $job->assertNotFailed()->assertNotReleased();
    $registros = array_values(array_filter($handler->getRecords(), fn ($registro) => $registro->message === 'fluxo.execucao'));
    expect($registros)->toHaveCount(1)
        ->and($registros[0]->context['resultado'])->toBe('falha_definitiva')
        ->and($registros[0]->level->getName())->toBe('ERROR');
});

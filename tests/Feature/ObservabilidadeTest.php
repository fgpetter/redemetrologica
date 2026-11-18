<?php

use App\Actions\Financeiro\EditarLancamentosEmLoteAction;
use App\Actions\Interlab\ProcessarInterlabLotePostagemAction;
use App\Actions\Interlab\RastrearItemLotePostagemAction;
use App\Actions\Interlab\ResultadoEtapaLotePostagem;
use App\Enums\InterlabLotePostagemStatus;
use App\Exceptions\CorreiosApiException;
use App\Exceptions\OperacaoImpedidaException;
use App\Integrations\Correios\CorreiosLog;
use App\Jobs\EnviaSenhaAnalistaJob;
use App\Jobs\EnviaSenhaLaboratorioJob;
use App\Jobs\Interlab\ProcessarInterlabLotePostagemJob;
use App\Jobs\Interlab\RastrearItemLotePostagemJob;
use App\Livewire\Interlab\GerarCertificadoButton;
use App\Models\LancamentoFinanceiro;
use App\Support\Observabilidade\ResumoExecucao;
use Database\Factories\InterlabLotePostagemFactory;
use Database\Factories\InterlabLotePostagemItemFactory;
use Illuminate\Database\DatabaseTransactionsManager;
use Illuminate\Log\LogManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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

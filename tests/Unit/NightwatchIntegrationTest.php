<?php

namespace Tests\Unit;

use App\Logging\ContextualIngest;
use App\Logging\NightwatchLogger;
use App\Support\NightwatchRedaction;
use App\Support\Observabilidade\ResumoExecucao;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Laravel\Nightwatch\Contracts\Ingest;
use Laravel\Nightwatch\Core;
use Laravel\Nightwatch\Factories\Logger;
use Mockery;
use Monolog\Handler\TestHandler;
use Monolog\Logger as Monolog;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class NightwatchIntegrationTest extends TestCase
{
    public static function executionModes(): array
    {
        return [
            'command' => ['command', ['command', 'exception']],
            'observability' => ['observability', ['command', 'exception', 'log']],
            'schedule' => ['schedule', ['scheduled-task']],
            'job' => ['job', ['queued-job', 'job-attempt']],
            'http' => ['http', ['request', 'exception']],
            'agent-unavailable' => ['agent-unavailable', []],
        ];
    }

    #[DataProvider('executionModes')]
    public function test_enabled_collection_in_isolated_processes(string $mode, array $expectedEvents): void
    {
        $process = new Process([PHP_BINARY, base_path('tests/Fixtures/nightwatch-smoke.php'), $mode], base_path());
        $process->setTimeout(20);
        $process->mustRun();
        $result = json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR);

        $this->assertTrue($result['enabled']);
        foreach ($expectedEvents as $event) {
            $this->assertGreaterThanOrEqual(1, $result['events'][$event] ?? 0);
        }
        if (in_array($mode, ['http', 'agent-unavailable'], true)) {
            $this->assertSame(200, $result['http_status']);
        }
    }

    public function test_reporting_an_exception_keeps_default_logging_without_email_or_queue(): void
    {
        Mail::fake();
        Queue::fake();
        Log::spy();
        $exception = new RuntimeException('Falha de integração controlada.');

        report($exception);

        Log::shouldHaveReceived('error')->once()->withArgs(fn ($message, $context) => $message === $exception->getMessage() && $context['exception'] === $exception);
        Mail::assertNothingOutgoing();
        Queue::assertNothingPushed();
    }

    public function test_nightwatch_is_disabled_in_tests_and_preserves_local_logging(): void
    {
        $this->assertFalse(app(Core::class)->enabled());
        $this->assertContains('single', config('logging.channels.stack.channels'));
        $this->assertContains('nightwatch', config('logging.channels.stack.channels'));
        $this->assertFalse(config('nightwatch.capture_request_payload'));
        $this->assertEquals(0.1, config('nightwatch.sampling.requests'));
        $this->assertEquals(1, config('nightwatch.sampling.exceptions'));
        $this->assertEquals(1, config('nightwatch.sampling.commands'));
        $this->assertEquals(1, config('nightwatch.sampling.scheduled_tasks'));
    }

    public function test_remote_log_redaction_does_not_change_the_local_record(): void
    {
        $captured = null;
        $core = app(Core::class);
        $core->config['enabled'] = true;
        $ingest = Mockery::mock(Ingest::class);
        $ingest->shouldReceive('write')->once()->withArgs(function (array $record) use (&$captured): bool {
            $captured = $record;

            return true;
        });
        $core->ingest = $ingest;
        $remote = (new NightwatchLogger(new Logger($core)))(['level' => 'info']);
        $local = new TestHandler;
        $stack = new Monolog('stack', [$local, ...$remote->getHandlers()]);

        $stack->error('Falha para pessoa@example.com token=segredo', [
            'etapa' => 'envio',
            'nested' => ['password' => 'senha-secreta'],
            'body' => ['dados' => 'privados'],
            'exception' => new RuntimeException('conteúdo privado'),
        ]);

        $this->assertStringContainsString('pessoa@example.com', $local->getRecords()[0]->message);
        $this->assertStringNotContainsString('pessoa@example.com', $captured['message']);
        $this->assertStringNotContainsString('segredo', $captured['message']);
        $context = json_decode($captured['context'], true);
        $this->assertSame('envio', $context['etapa']);
        $this->assertSame('[redacted]', $context['nested']['password']);
        $this->assertSame('[redacted]', $context['body']);
        $this->assertSame(RuntimeException::class, $context['exception']);
    }

    public function test_sensitive_urls_and_sql_values_are_removed_from_messages(): void
    {
        $message = NightwatchRedaction::text('Falha https://example.com/reset/segredo?email=pessoa@example.com Bearer credencial SQL: insert into users values (\'privado\')');

        foreach (['segredo', 'pessoa@example.com', 'credencial', 'privado'] as $secret) {
            $this->assertStringNotContainsString($secret, $message);
        }

        $this->assertSame('https://example.com', NightwatchRedaction::url('https://user:pass@example.com/reset/segredo?token=privado'));
    }

    public function test_application_logging_works_when_nightwatch_is_disabled(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'nightwatch-local-');
        config(['logging.channels.single.path' => $path]);
        Log::forgetChannel('single');
        Log::forgetChannel('stack');

        try {
            Log::error('Falha controlada sem agente.');
            $this->assertStringContainsString('Falha controlada sem agente.', file_get_contents($path));
        } finally {
            Log::forgetChannel('stack');
            Log::forgetChannel('single');
            unlink($path);
        }
    }

    public function test_operational_destination_only_forwards_marked_safe_metadata(): void
    {
        $core = app(Core::class);
        $core->config['enabled'] = true;
        $records = [];
        $ingest = Mockery::mock(Ingest::class);
        $ingest->shouldReceive('write')->once()->withArgs(function (array $record) use (&$records): bool {
            $records[] = $record;

            return true;
        });
        $core->ingest = $ingest;
        $remote = (new NightwatchLogger(new Logger($core)))(['level' => 'info', 'operacional' => true]);
        $local = new TestHandler;
        $stack = new Monolog('operacional', [$local, ...$remote->getHandlers()]);
        $stack->info('legado privado@example.com', ['email' => 'privado@example.com']);
        $stack->debug('detalhe');
        $stack->info('mensagem livre privada', [
            'evento' => 'fluxo.execucao', 'fluxo' => 'certificado', 'resultado' => 'enfileirado',
            'tentativa_id' => 'tentativa-1', 'body' => 'PDFSECRETO', 'email' => 'privado@example.com',
            'etapas' => [['etapa' => 'enfileirar', 'resultado' => 'concluido', 'duracao_ms' => 2]],
            'metricas' => ['enfileirados' => 1],
        ]);
        $this->assertCount(3, $local->getRecords());
        $this->assertSame('fluxo.execucao', $records[0]['message']);
        $context = json_decode($records[0]['context'], true);
        $this->assertSame('tentativa-1', $context['tentativa_id']);
        $this->assertSame(1, $context['metricas']['enfileirados']);
        $this->assertStringNotContainsString('privada', json_encode($records));
        $this->assertStringNotContainsString('PDFSECRETO', json_encode($records));
        $this->assertArrayNotHasKey('email', $context);
    }

    public function test_exception_record_receives_only_the_scoped_item_correlation(): void
    {
        $records = [];
        $sink = Mockery::mock(Ingest::class);
        $sink->shouldReceive('write')->andReturnUsing(function (array $record) use (&$records): void {
            $records[] = $record;
        });
        $ingest = new ContextualIngest($sink);
        $principal = new ResumoExecucao('teste.lote', ['agenda_uid' => 'agenda-1']);
        $item = $principal->item('teste.item', ['agenda_uid' => 'agenda-1', 'inscrito_id' => 7, 'tentativa_id' => 'tentativa-1']);

        Context::flush();
        $ingest->write(['t' => 'exception', 'message' => 'fora do escopo']);
        Context::scope(fn () => $ingest->write(['t' => 'exception', 'message' => 'no escopo']), $item->correlacao());
        Context::scope(fn () => $ingest->write(['t' => 'log', 'message' => 'log comum']), $item->correlacao());

        $this->assertArrayNotHasKey('context', $records[0]);
        $contexto = json_decode($records[1]['context'], true);
        $this->assertSame('teste.item', $contexto['fluxo']);
        $this->assertSame($item->correlacao()['execucao_id'], $contexto['execucao_id']);
        $this->assertSame($principal->correlacao()['execucao_id'], $contexto['execucao_pai_id']);
        $this->assertSame(7, $contexto['inscrito_id']);
        $this->assertSame('tentativa-1', $contexto['tentativa_id']);
        $this->assertSame('agenda-1', $contexto['agenda_uid']);
        $this->assertArrayNotHasKey('context', $records[2]);
        $this->assertSame([], Context::all());
    }

    public function test_scoped_context_is_restored_after_success_and_exception(): void
    {
        Context::flush();
        Context::add('existente', 'mantido');
        $item = (new ResumoExecucao('teste.lote'))->item('teste.item', ['inscrito_id' => 9]);

        Context::scope(fn () => null, $item->correlacao());

        try {
            Context::scope(fn () => throw new RuntimeException('falha sintetica'), $item->correlacao());
        } catch (RuntimeException) {
            //
        }

        $this->assertSame(['existente' => 'mantido'], Context::all());
        Context::flush();
    }
}

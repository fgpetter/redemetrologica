<?php

use App\Support\Observabilidade\ResumoExecucao;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Laravel\Nightwatch\Contracts\Ingest;
use Laravel\Nightwatch\Core;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

require dirname(__DIR__, 2).'/vendor/autoload.php';
foreach (['APP_ENV' => 'local', 'NIGHTWATCH_CAPTURE_EXCEPTION_SOURCE_CODE' => 'false', 'NIGHTWATCH_ENABLED' => 'true', 'NIGHTWATCH_REQUEST_SAMPLE_RATE' => '1', 'QUEUE_CONNECTION' => 'sync', 'CACHE_DRIVER' => 'array', 'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => ':memory:', 'LOG_CHANNEL' => 'stack', 'LOG_STACK' => 'single,nightwatch', 'NIGHTWATCH_INGEST_URI' => '127.0.0.1:1'] as $key => $value) {
    putenv("$key=$value");
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}
if (in_array($argv[1] ?? '', ['http', 'agent-unavailable'], true)) {
    putenv('NIGHTWATCH_FORCE_REQUEST=true');
    $_ENV['NIGHTWATCH_FORCE_REQUEST'] = 'true';
    $_SERVER['NIGHTWATCH_FORCE_REQUEST'] = 'true';
}
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$ingest = new class implements Ingest
{
    public array $records = [];

    public function write(array $record): void
    {
        $this->records[] = json_decode(json_encode($record), true);
    }

    public function writeNow(array $record): void
    {
        $this->records[] = json_decode(json_encode($record), true);
    }

    public function ping(): void {}

    public function shouldDigest(bool $bool = true): void {}

    public function shouldDigestWhenBufferIsFull(bool $bool = true): void {}

    public function digest(): void {}

    public function flush(): void {}
};
$core = $app->make(Core::class);
if (($argv[1] ?? '') !== 'agent-unavailable') {
    $core->ingest = $ingest;
}
class NightwatchSmokeJob implements ShouldQueue
{
    public function handle(): void
    {
        Log::info('nightwatch smoke job');
    }
}
if (in_array($argv[1] ?? '', ['http', 'agent-unavailable'], true)) {
    Route::get('/_nightwatch-smoke/{token}', function () {
        report(new RuntimeException('nightwatch smoke HTTP exception'));

        return response('ok');
    });
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $request = Request::create('/_nightwatch-smoke/secret-path?token=secret-query', 'GET', [], [], [], ['HTTP_AUTHORIZATION' => 'Bearer secret-header']);
    $response = $kernel->handle($request);
    $kernel->terminate($request, $response);
} else {
    $mode = $argv[1] ?? 'command';
    $kernel = $app->make(Kernel::class);
    if ($mode === 'schedule') {
        $schedule = new Schedule;
        $schedule->call(fn () => Log::info('nightwatch smoke scheduled task'))->name('nightwatch-smoke-task')->everyMinute();
        $app->instance(Schedule::class, $schedule);
        $input = new ArrayInput(['command' => 'schedule:run']);
    } elseif ($mode === 'job') {
        config(['queue.default' => 'database']);
        Schema::create('jobs', function ($table) {
            $table->bigIncrements('id');
            $table->string('queue');
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });
        dispatch(new NightwatchSmokeJob);
        $input = new ArrayInput(['command' => 'queue:work', '--once' => true]);
    } else {
        Artisan::command('nightwatch:smoke', function () use ($mode) {
            if ($mode === 'observability') {
                $summary = new ResumoExecucao('smoke.consumida', ['tentativa_id' => 'tentativa-smoke'], 'mailsent');
                $summary->executar(function ($summary) {
                    try {
                        $summary->etapa('enviar', fn () => throw new RuntimeException('falha sintetica consumida'));
                    } catch (RuntimeException $exception) {
                        report($exception);
                        $summary->resultado('falha_definitiva', 'erro_tecnico');
                    }
                });
            } else {
                report(new RuntimeException('nightwatch smoke exception'));
            }
        });
        $input = new ArrayInput(['command' => 'nightwatch:smoke']);
    }
    $status = $kernel->handle($input, new NullOutput);
    $kernel->terminate($input, $status);
}

$core->digest();
$types = array_count_values(array_column($ingest->records, 't'));
$payload = json_encode($ingest->records);
if (str_contains($payload, 'secret-path') || str_contains($payload, 'secret-query') || str_contains($payload, 'secret-header')) {
    throw new RuntimeException('Sensitive HTTP data leaked');
}
if (($argv[1] ?? '') === 'observability') {
    $exceptions = array_values(array_filter($ingest->records, fn ($record) => $record['t'] === 'exception'));
    if (count($exceptions) !== 1 || empty($exceptions[0]['trace'])) {
        throw new RuntimeException('Expected one consumed exception with stack trace');
    }
}
echo json_encode(['enabled' => $core->enabled(), 'events' => $types, 'http_status' => isset($response) ? $response->getStatusCode() : null], JSON_PRETTY_PRINT).PHP_EOL;

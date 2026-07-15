# Referência — Sail debug logging

## Middleware temporário (template)

```php
<?php

namespace App\Http\Middleware;

use App\Support\DebugRequestLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class DebugRequestQueries
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->shouldDebug($request)) {
            return $next($request);
        }

        app()->instance('debug.request_log', true);
        DB::enableQueryLog();

        DebugRequestLog::write('DebugRequestQueries::handle', 'Request started', [
            'path' => $request->path(),
        ], 'init');

        $response = $next($request);

        $this->writeQuerySummary($request, DB::getQueryLog());

        return $response;
    }

    private function shouldDebug(Request $request): bool
    {
        return str_contains($request->path(), 'SUBSTITUIR_SEGMENTO_ROTA');
    }

  /**
   * @param  array<int, array{query: string, bindings: array, time: float}>  $queries
   */
    private function writeQuerySummary(Request $request, array $queries): void
    {
        $byTable = [];
        foreach ($queries as $q) {
            if (preg_match('/from\s+[`"]?(\w+)[`"]?/i', $q['query'] ?? '', $m)) {
                $byTable[$m[1]] = ($byTable[$m[1]] ?? 0) + 1;
            }
        }
        arsort($byTable);

        DebugRequestLog::write(
            'DebugRequestQueries::writeQuerySummary',
            'Request finished',
            [
                'totalQueries' => count($queries),
                'topTables' => array_slice($byTable, 0, 10, true),
                'path' => $request->path(),
            ],
            'summary',
        );
    }
}
```

Registro na rota (remover depois):

```php
Route::get('caminho', [Controller::class, 'metodo'])
    ->middleware(\App\Http\Middleware\DebugRequestQueries::class);
```

## Simular request autenticada (Sail tinker)

```bash
vendor/bin/sail artisan tinker --execute '
$user = \App\Models\User::factory()->create();
$permission = \App\Models\Permission::withoutEvents(
    fn () => \App\Models\Permission::firstOrCreate(["permission" => "funcionario"])
);
$user->permissions()->syncWithoutDetaching([$permission->id]);
\Illuminate\Support\Facades\Auth::login($user);
$kernel = app(\Illuminate\Contracts\Http\Kernel::class);
$request = \Illuminate\Http\Request::create("/painel/ROTA", "GET");
$response = $kernel->handle($request);
echo "STATUS:".$response->getStatusCode()."\n";
$kernel->terminate($request, $response);
'
```

## Ler e analisar logs (agente)

```bash
# Últimas linhas do log da sessão
tail -n 20 storage/logs/debug-SESSION_ID.log

# Contar eventos por hypothesisId (jq no host)
jq -r '.hypothesisId' storage/logs/debug-SESSION_ID.log | sort | uniq -c
```

## Comparar pre-fix vs post-fix

Mesmo `sessionId`, `runId` diferente no payload. Linha `summary` → campo `data.totalQueries`.

Exemplo de conclusão:

- `H-C` CONFIRMADA: `inscritoIdQueries` caiu de 25 para 2.
- `H-D` CONFIRMADA: `analistasQueries` caiu de 26 para 1.

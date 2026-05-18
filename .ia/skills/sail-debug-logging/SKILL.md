---
name: sail-debug-logging
description: >-
  Instrumenta e analisa logs de debug em projetos Laravel Sail (Docker): NDJSON,
  contagem de queries, checkpoints e caminhos compatíveis com o container.
  Use em debug mode, investigação de N+1, performance de páginas Livewire, ou quando
  o usuário pedir logs de runtime no ambiente Sail.
license: MIT
---

# Debug logging no Laravel Sail

Este projeto roda em **Laravel Sail** (`vendor/bin/sail`). O PHP executa **dentro do container**; caminhos e permissões do host não são confiáveis.

## Regras obrigatórias

1. **Nunca** use caminho absoluto do host (ex.: `/home/user/projects/...`) em `file_put_contents`.
2. **Sempre** use `base_path()` para arquivos no repositório.
3. **Prefira** `storage/logs/debug-{sessionId}.log` se `.cursor/` falhar por permissão no container.
4. **Limpe** o arquivo de log antes de cada execução de teste (ferramenta `delete_file`, não `rm`).
5. **Remova** toda instrumentação após confirmar o fix (middleware, checkpoints, helper) — logs são temporários.
6. Comandos de verificação: `vendor/bin/sail artisan ...`, `vendor/bin/sail artisan test --compact`.

## Onde gravar logs

| Contexto | Destino recomendado |
|----------|---------------------|
| PHP (Sail) | `base_path('storage/logs/debug-{sessionId}.log')` |
| PHP (alternativa Cursor) | `base_path('.cursor/debug-{sessionId}.log')` — só se gravável no container |
| JavaScript no browser | POST para o endpoint de ingest do debug mode (quando fornecido na sessão) |

Garantir diretório antes de escrever:

```php
$logPath = base_path('storage/logs/debug-'.$sessionId.'.log');
$dir = dirname($logPath);
if (! is_dir($dir)) {
    mkdir($dir, 0755, true);
}
file_put_contents($logPath, $line, FILE_APPEND | LOCK_EX);
```

## Formato NDJSON (uma linha JSON por evento)

```json
{
  "sessionId": "abc123",
  "runId": "pre-fix",
  "hypothesisId": "H-A",
  "location": "App\\Livewire\\Foo::render",
  "message": "Render end",
  "data": { "queryCountSoFar": 12, "count": 25 },
  "timestamp": 1779132504434
}
```

- `sessionId`: ID da sessão de debug (quando existir no system reminder).
- `runId`: `pre-fix` / `post-fix` para comparar antes e depois.
- `hypothesisId`: liga o log à hipótese testada.
- **Não** logar PII, tokens, senhas ou conteúdo de `.env`.

## Helper PHP mínimo

Criar classe temporária (ex.: `app/Support/DebugRequestLog.php`) ou métodos estáticos no middleware. Remover ao final.

```php
<?php

namespace App\Support;

final class DebugRequestLog
{
    private const SESSION_ID = 'SUBSTITUIR'; // da sessão de debug

    public static function isEnabled(): bool
    {
        return app()->bound('debug.request_log')
            && app('debug.request_log') === true;
    }

    public static function write(
        string $location,
        string $message,
        array $data = [],
        string $hypothesisId = 'checkpoint',
        string $runId = 'pre-fix',
    ): void {
        if (! self::isEnabled()) {
            return;
        }

        // #region agent log
        $payload = json_encode([
            'sessionId' => self::SESSION_ID,
            'runId' => $runId,
            'hypothesisId' => $hypothesisId,
            'location' => $location,
            'message' => $message,
            'data' => $data,
            'timestamp' => (int) (microtime(true) * 1000),
        ], JSON_UNESCAPED_UNICODE);

        if ($payload === false) {
            return;
        }

        $logPath = base_path('storage/logs/debug-'.self::SESSION_ID.'.log');
        $dir = dirname($logPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($logPath, $payload."\n", FILE_APPEND | LOCK_EX);
        // #endregion
    }
}
```

Envolver **cada** chamada de log em `// #region agent log` … `// #endregion` para o editor recolher o bloco.

## Contagem de queries (investigação N+1)

### Middleware temporário na rota

1. Registrar middleware só na rota investigada (não no grupo `web` global).
2. Em `handle()`: `app()->instance('debug.request_log', true)` + `DB::enableQueryLog()`.
3. Ao final da request: `DB::getQueryLog()`, agregar totais e padrões SQL.
4. Remover middleware e rota extra ao concluir.

Campos úteis no resumo:

- `totalQueries`
- `topTables` (contagem por tabela no `FROM`)
- Contadores por padrão (`interlab_analistas`, `json_contains`, etc.)

### Checkpoints em pontos-chave

Inserir **2–6** logs, não dezenas:

- Fim do controller, antes da view.
- Início/fim do `render()` Livewire crítico.
- Após eager loads.

Incluir `queryCountSoFar` via `count(DB::getQueryLog())` quando o log de queries estiver ativo.

```php
DebugRequestLog::write(
    'ListParticipantes::render',
    'Render end',
    ['inscritosCount' => $items->count(), 'queryCountSoFar' => count(DB::getQueryLog())],
    'H-B',
);
```

## JavaScript (Blade / Livewire no browser)

Quando o debug mode fornecer endpoint e `sessionId`:

```javascript
// #region agent log
fetch('ENDPOINT_DA_SESSAO', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'X-Debug-Session-Id': 'SESSION_ID',
  },
  body: JSON.stringify({
    sessionId: 'SESSION_ID',
    location: 'file.js:42',
    message: 'desc',
    data: { k: v },
    timestamp: Date.now(),
    hypothesisId: 'H-A',
  }),
}).catch(() => {});
// #endregion
```

Use o endpoint **exato** do system reminder; não hardcodar URL de outra sessão.

## Fluxo de trabalho do agente

1. Formular 3–5 hipóteses com IDs (`H-A`, `H-B`, …).
2. Instrumentar para testar todas em paralelo.
3. Limpar o arquivo de log (`delete_file` no path do projeto).
4. Pedir reprodução ou executar via Sail:
   ```bash
   vendor/bin/sail artisan tinker --execute '
   $user = \App\Models\User::factory()->create();
   // permissões, login, $kernel->handle(Request::create(...))
   '
   ```
5. Ler o NDJSON no host (`storage/logs/debug-*.log` ou `.cursor/debug-*.log`).
6. Marcar hipóteses CONFIRMADA / REJEITADA com evidência de linha.
7. Corrigir só com evidência; manter logs na verificação pós-fix (`runId: post-fix`).
8. Remover instrumentação e arquivo helper após confirmação do usuário.

## Armadilhas deste projeto (Sail + WSL)

| Problema | Sintoma | Solução |
|----------|---------|---------|
| Path do host no PHP | `Failed to open stream` no container | `base_path('storage/logs/...')` |
| `.cursor/` sem permissão | `mkdir(): Permission denied` | Usar `storage/logs/` |
| Log no host, app no container | Arquivo vazio no host | Path via `base_path()` (volume montado em `/var/www/html`) |
| `DB::getQueryLog()` vazio | Contagem 0 | Chamar `DB::enableQueryLog()` antes da request processada |
| Instrumentação permanece | Código de debug em PR | Remover middleware, imports e `#region agent log` |

## O que não fazer

- Não commitar middleware/helpers de debug sem pedido explícito.
- Não usar `setTimeout`/`sleep` como “fix”.
- Não acumular guards de hipóteses rejeitadas nos logs.
- Não ultrapassar ~10 pontos de log por fluxo.
- Não rodar `rm`/`touch` no log — usar `delete_file` do agente.

## Limpeza obrigatória ao terminar

- [ ] Deletar middleware temporário e registro em `routes/web.php`
- [ ] Remover `use` e chamadas `DebugRequestLog::write` / checkpoints
- [ ] Deletar `app/Support/DebugRequestLog.php` (se criado)
- [ ] Opcional: apagar `storage/logs/debug-*.log` da sessão

## Recursos

- Padrões Livewire do projeto: [project-livewire-patterns/SKILL.md](../project-livewire-patterns/SKILL.md)
- Performance DB Laravel: [laravel-best-practices](../../../.claude/skills/laravel-best-practices/SKILL.md) (regra `db-performance`)
- Templates PHP/JS completos: [reference.md](reference.md)

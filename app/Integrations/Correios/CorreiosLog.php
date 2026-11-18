<?php

namespace App\Integrations\Correios;

use App\Exceptions\CorreiosApiException;
use App\Support\Observabilidade\ContextoOperacional;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;
use Throwable;

class CorreiosLog
{
    public const CHANNEL = 'correios';

    public static function info(string $evento, array $contexto = []): void
    {
        // Etapas detalhadas ficam no arquivo; o resumo é o marco operacional.
        Log::channel(self::CHANNEL)->debug($evento, ContextoOperacional::selecionar($contexto));
    }

    public static function warning(string $evento, array $contexto = []): void
    {
        Log::channel(self::CHANNEL)->debug($evento, ContextoOperacional::selecionar($contexto));
    }

    public static function error(string $evento, array $contexto = []): void
    {
        Log::channel(self::CHANNEL)->log($evento === 'lote.encerrado' || str_ends_with($evento, '.failed') ? 'error' : 'debug', $evento, ContextoOperacional::selecionar(['evento' => $evento, ...$contexto]));
    }

    public static function concluido(array $contexto): void
    {
        Log::channel(self::CHANNEL)->info('lote.concluido', ContextoOperacional::selecionar(['evento' => 'lote.concluido', 'resultado' => 'concluido', ...$contexto]));
    }

    public static function request(Request $request): void
    {
        self::info('http.request', ['method' => $request->method()]);
    }

    public static function response(string $etapa, Response $response): void
    {
        self::info('http.response', [
            'etapa' => $etapa,
            'status_http' => $response->status(),
            'bytes' => strlen($response->body()),
        ]);
    }

    public static function exception(string $evento, Throwable $exception, array $contexto = []): void
    {
        // Chamadores decidem se a causa é esperada/consumida; worker reporta propagadas.
        $transitoria = $exception instanceof ConnectionException
            || ($exception instanceof CorreiosApiException && ($exception->status === 429 || $exception->status >= 500));
        if ($transitoria) {
            self::warning($evento, [...$contexto, 'resultado' => 'retentativa', 'motivo' => 'api_transitoria']);
        } else {
            self::error($evento, $contexto);
        }
    }
}

<?php

namespace App\Integrations\Correios;

use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;
use Throwable;

class CorreiosLog
{
    public const CHANNEL = 'correios';

    /**
     * @param  array<string, mixed>  $contexto
     */
    public static function info(string $evento, array $contexto = []): void
    {
        Log::channel(self::CHANNEL)->info($evento, self::redigir($contexto));
    }

    /**
     * @param  array<string, mixed>  $contexto
     */
    public static function warning(string $evento, array $contexto = []): void
    {
        Log::channel(self::CHANNEL)->warning($evento, self::redigir($contexto));
    }

    /**
     * @param  array<string, mixed>  $contexto
     */
    public static function error(string $evento, array $contexto = []): void
    {
        Log::channel(self::CHANNEL)->error($evento, self::redigir($contexto));
    }

    public static function request(Request $request): void
    {
        self::info('http.request', [
            'method' => $request->method(),
            'url' => $request->url(),
            'body' => $request->data(),
        ]);
    }

    public static function response(string $etapa, Response $response): void
    {
        $body = $response->body();
        $json = $response->json();
        $contentType = (string) $response->header('Content-Type');

        self::info('http.response', [
            'etapa' => $etapa,
            'status' => $response->status(),
            'content_type' => $contentType,
            'retry_after' => $response->header('Retry-After'),
            'body_bytes' => strlen($body),
            'chaves' => is_array($json) ? array_keys($json) : [],
            'body' => self::resumirCorpo($json, $body, $contentType),
        ]);
    }

    public static function exception(string $evento, Throwable $exception, array $contexto = []): void
    {
        self::error($evento, [
            ...$contexto,
            'exception' => $exception::class,
            'mensagem' => $exception->getMessage(),
        ]);
    }

    /**
     * @param  array<string, mixed>|null  $json
     * @return array<string, mixed>|string|null
     */
    private static function resumirCorpo(mixed $json, string $body, string $contentType): mixed
    {
        if (is_array($json)) {
            return self::resumirJson($json);
        }

        if ($body === '') {
            return null;
        }

        if (str_contains(strtolower($contentType), 'html') || str_starts_with(ltrim($body), '<')) {
            return [
                'html_bytes' => strlen($body),
                'html_trecho' => mb_substr($body, 0, 200),
            ];
        }

        return mb_substr($body, 0, 500);
    }

    /**
     * @param  array<string, mixed>  $json
     * @return array<string, mixed>
     */
    private static function resumirJson(array $json): array
    {
        $resumo = [];

        foreach ($json as $chave => $valor) {
            if ($chave === 'dados' && is_string($valor)) {
                $decodificado = base64_decode($valor, true) ?: '';
                $resumo['dados_presente'] = true;
                $resumo['dados_bytes'] = strlen($decodificado);
                $resumo['dados_assinatura'] = str_starts_with($decodificado, '%PDF') ? '%PDF' : mb_substr($decodificado, 0, 16);

                continue;
            }

            if (is_string($valor) && strlen($valor) > 2000) {
                $resumo[$chave] = [
                    'omitido' => true,
                    'bytes' => strlen($valor),
                    'trecho' => mb_substr($valor, 0, 200),
                ];

                continue;
            }

            if (is_array($valor)) {
                $resumo[$chave] = self::resumirJson($valor);

                continue;
            }

            $resumo[$chave] = $valor;
        }

        return $resumo;
    }

    /**
     * @param  array<string, mixed>  $contexto
     * @return array<string, mixed>
     */
    private static function redigir(array $contexto): array
    {
        foreach ($contexto as $chave => $valor) {
            if (is_array($valor)) {
                $contexto[$chave] = self::redigir($valor);

                continue;
            }

            if (! is_string($chave)) {
                continue;
            }

            if (preg_match('/(token|authorization|password|api_key|codigo_acesso|secret)/i', $chave) === 1) {
                $contexto[$chave] = '[redacted]';
            }
        }

        return $contexto;
    }
}

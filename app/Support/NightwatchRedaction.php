<?php

namespace App\Support;

final class NightwatchRedaction
{
    public static function text(string $value): string
    {
        return preg_replace([
            '~https?://[^\s<>"\']+~i',
            '/\bBearer\s+\S+/i',
            '/\b[\w.+-]+@[\w.-]+\.[a-z]{2,}\b/i',
            '/\b(password|senha|token|secret|authorization|api[_-]?key|codigo_acesso)\b\s*[=:]\s*("[^"]*"|\'[^\']*\'|[^\s,;]+)/i',
            '/\bSQL:.*$/s',
        ], [
            '[url omitida]',
            'Bearer [redacted]',
            '[email omitido]',
            '$1=[redacted]',
            'SQL: [omitido]',
        ], $value) ?? '[omitido]';
    }

    public static function url(string $url): string
    {
        $host = parse_url($url, PHP_URL_HOST);

        return $host ? 'https://'.$host : '[url omitida]';
    }

    /** @param array<string|int, mixed> $context */
    public static function context(array $context): array
    {
        foreach ($context as $key => $value) {
            if (preg_match('/password|senha|token|secret|authorization|cookie|api.?key|codigo.?acesso|email|cpf|cnpj|telefone|endereco|address|banc|conta|agencia|body|payload|request|response|dados|content/i', (string) $key)) {
                $context[$key] = '[redacted]';
            } elseif (is_array($value)) {
                $context[$key] = self::context($value);
            } elseif (is_string($value)) {
                $context[$key] = self::text($value);
            } elseif (is_object($value)) {
                $context[$key] = $value::class;
            }
        }

        return $context;
    }
}

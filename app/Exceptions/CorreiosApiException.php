<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Client\Response;

class CorreiosApiException extends Exception
{
    /**
     * @param  array<string, mixed>|null  $body
     */
    public function __construct(
        string $message,
        public int $status,
        public ?array $body,
        public string $path,
    ) {
        parent::__construct($message);
    }

    public static function fromResponse(Response $response, string $path): self
    {
        $json = $response->json();
        $body = is_array($json) ? $json : ['raw' => $response->body()];

        return new self(
            "Falha na API Correios ({$response->status()}) em {$path}: ".self::detalhe($body, $response->body()),
            $response->status(),
            $body,
            $path,
        );
    }

    /**
     * @return array{status: int, path: string, body: array<string, mixed>|null}
     */
    public function context(): array
    {
        return [
            'status' => $this->status,
            'path' => $this->path,
            'body' => $this->body,
        ];
    }

    /**
     * @param  array<string, mixed>  $body
     */
    private static function detalhe(array $body, string $raw): string
    {
        $mensagem = $body['msgs'][0] ?? $body['mensagem'] ?? $body['causa'] ?? $raw;

        if (is_array($mensagem)) {
            $mensagem = $mensagem['mensagem'] ?? $mensagem['causa'] ?? json_encode($mensagem);
        }

        return mb_substr((string) $mensagem, 0, 500);
    }
}

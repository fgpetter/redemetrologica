<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ProcessosAgendados
{
    public const FILA_EMAIL = 'emails-agendados';

    private static array $contexto = [];

    private static array $resumo = [];

    public static function contexto(): array
    {
        return self::$contexto;
    }

    public static function executar(array $contexto, callable $callback): mixed
    {
        $anterior = self::$contexto;
        self::$contexto = [...$anterior, ...$contexto];

        try {
            return $callback();
        } finally {
            self::$contexto = $anterior;
        }
    }

    public static function execucao(string $processo, callable $callback): mixed
    {
        return self::executar(['processo' => $processo, 'execucao_id' => (string) Str::uuid()], function () use ($callback) {
            $resumoAnterior = self::$resumo;
            self::$resumo = [];

            try {
                $inicio = hrtime(true);
                self::log('processo.inicio');

                try {
                    $resultado = $callback();
                } catch (Throwable $exception) {
                    self::log('processo.falha', [
                        ...self::$resumo,
                        'exception' => $exception::class,
                        'duracao_ms' => self::duracao($inicio),
                    ], 'error');

                    throw $exception;
                }

                $falhou = is_int($resultado) && $resultado !== 0;
                self::log($falhou ? 'processo.falha' : 'processo.concluido', [
                    ...self::$resumo,
                    'duracao_ms' => self::duracao($inicio),
                    'codigo_saida' => is_int($resultado) ? $resultado : null,
                ], $falhou ? 'error' : 'info');

                return $resultado;
            } finally {
                self::$resumo = $resumoAnterior;
            }
        });
    }

    public static function resumo(array $contagens): void
    {
        self::$resumo = [...self::$resumo, ...$contagens];
    }

    public static function log(string $evento, array $contexto = [], string $nivel = 'info'): void
    {
        Log::channel('processos_agendados')->log($nivel, $evento, [...self::$contexto, ...$contexto]);
    }

    private static function duracao(int $inicio): int
    {
        return (int) round((hrtime(true) - $inicio) / 1_000_000);
    }
}

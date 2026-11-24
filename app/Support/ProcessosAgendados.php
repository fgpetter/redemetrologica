<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ProcessosAgendados
{
    public const CONTEXTO_EMAIL = 'email_agendado';

    private static ?CarbonImmutable $proximoEnvio = null;

    private static array $resumo = [];

    private static array $enviosRodada = [];

    public static function emRodada(): bool
    {
        return Context::has('rodada_id');
    }

    public static function dataReferencia(): CarbonImmutable
    {
        return CarbonImmutable::parse(Context::get('data_rodada', 'today'), 'America/Sao_Paulo');
    }

    public static function rodada(CarbonImmutable $inicio, callable $callback): int
    {
        self::$proximoEnvio = $inicio;
        self::$enviosRodada = ['emails_enfileirados' => 0];

        try {
            return Context::scope(fn (): int => self::execucao('agendamentos:executar-rodada', function () use ($callback): int {
                try {
                    return $callback();
                } finally {
                    self::resumo(self::$enviosRodada);
                }
            }), [
                'rodada_id' => (string) Str::uuid(),
                'data_rodada' => $inicio->toDateString(),
            ]);
        } finally {
            self::$proximoEnvio = null;
            self::$enviosRodada = [];
        }
    }

    public static function despachar(object $job, bool $agendado): void
    {
        if ($agendado) {
            self::enfileirar(fn (CarbonImmutable $horario) => Bus::dispatch(
                $job->onConnection('database')->delay($horario),
            ));

            return;
        }

        Bus::dispatch($job);
    }

    /** @param callable(CarbonImmutable): mixed $despachar */
    public static function enfileirar(callable $despachar): void
    {
        $agora = CarbonImmutable::now();
        $horario = self::$proximoEnvio?->max($agora) ?? $agora;

        if (self::comContextoDeEmail(fn () => $despachar($horario)) === null) {
            return;
        }

        $registrar = static function () use ($horario): void {
            self::$proximoEnvio = $horario->addSeconds(5);
            self::registrarEnvio(self::$resumo, $horario->getTimestamp());
            self::registrarEnvio(self::$enviosRodada, $horario->getTimestamp());
        };

        // Os jobs transacionais são gravados com a reserva de negócio na mesma conexão.
        if (DB::transactionLevel() > 0) {
            DB::afterCommit($registrar);
        } else {
            $registrar();
        }
    }

    public static function comContextoDeEmail(callable $despachar): mixed
    {
        return Context::scope($despachar, hidden: [self::CONTEXTO_EMAIL => true]);
    }

    private static function registrarEnvio(array &$resumo, int $horario): void
    {
        $resumo['emails_enfileirados'] = ($resumo['emails_enfileirados'] ?? 0) + 1;
        $resumo['primeiro_disponivel_em'] ??= $horario;
        $resumo['ultimo_disponivel_em'] = $horario;
    }

    public static function execucao(string $processo, callable $callback): mixed
    {
        return Context::scope(function () use ($callback) {
            $resumoAnterior = self::$resumo;
            self::$resumo = ['emails_enfileirados' => 0];

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
        }, ['processo' => $processo, 'execucao_id' => (string) Str::uuid()]);
    }

    public static function resumo(array $contagens): void
    {
        self::$resumo = [...self::$resumo, ...$contagens];
    }

    public static function log(string $evento, array $contexto = [], string $nivel = 'info'): void
    {
        Log::channel('processos_agendados')->log($nivel, $evento, $contexto);
    }

    private static function duracao(int $inicio): int
    {
        return (int) round((hrtime(true) - $inicio) / 1_000_000);
    }
}

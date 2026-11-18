<?php

namespace App\Support\Observabilidade;

use App\Exceptions\OperacaoImpedidaException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

/** Estado pertence somente à execução atual; não registrar como singleton. */
final class ResumoExecucao
{
    private int $inicio;

    private array $etapas = [];

    private array $metricas = [];

    private string $resultado = 'concluido';

    private ?string $motivo = null;

    private bool $encerrado = false;

    public function __construct(private string $fluxo, private array $referencias = [], private string $canal = 'stack')
    {
        $this->inicio = hrtime(true);
        $this->referencias['execucao_id'] = (string) Str::uuid();
    }

    public function contexto(array $referencias): void
    {
        $this->referencias = [...$this->referencias, ...$referencias];
    }

    public function resultado(string $resultado, ?string $motivo = null): void
    {
        $this->resultado = $resultado;
        $this->motivo = $motivo;
    }

    public function incrementar(string $metrica, int $quantidade = 1): void
    {
        $this->metricas[$metrica] = ($this->metricas[$metrica] ?? 0) + $quantidade;
    }

    public function etapa(string $nome, callable $acao): mixed
    {
        $inicio = hrtime(true);
        $this->etapas[$nome] ??= ['etapa' => $nome, 'resultado' => 'concluido', 'execucoes' => 0, 'duracao_ms' => 0];
        $resultado = 'concluido';
        try {
            return $acao();
        } catch (Throwable $exception) {
            $resultado = ($exception instanceof ValidationException || $exception instanceof OperacaoImpedidaException) ? 'impedido' : 'falha';
            throw $exception;
        } finally {
            // Agregar repetições de uma mesma etapa em lotes; memória limitada ao vocabulário do fluxo.
            $etapa = $this->etapas[$nome] ?? ['etapa' => $nome, 'resultado' => 'concluido', 'execucoes' => 0, 'duracao_ms' => 0];
            $etapa['execucoes']++;
            $etapa['duracao_ms'] += round((hrtime(true) - $inicio) / 1_000_000, 2);
            if ($resultado !== 'concluido') {
                $etapa['resultado'] = $resultado;
            }
            $this->etapas[$nome] = $etapa;
        }
    }

    public function resultadoEtapa(string $nome, string $resultado): void
    {
        if (isset($this->etapas[$nome])) {
            $this->etapas[$nome]['resultado'] = $resultado;
        }
    }

    /** Não reporta a exceção propagada: essa responsabilidade continua com o chamador/framework. */
    public function executar(callable $acao, bool $job = false): mixed
    {
        try {
            return $this->etapa('processar', fn () => $acao($this));
        } catch (ValidationException $exception) {
            $this->resultado('impedido', 'validacao');
            throw $exception;
        } catch (OperacaoImpedidaException $exception) {
            $this->resultado('impedido', $exception->motivo);
            throw $exception;
        } catch (Throwable $exception) {
            $this->resultado($job ? 'falha_execucao' : 'falha_definitiva', 'erro_tecnico');
            throw $exception;
        } finally {
            $this->encerrar();
        }
    }

    public function encerrar(): void
    {
        if ($this->encerrado) {
            return;
        }
        $this->encerrado = true;
        $this->resultadoEtapa('processar', $this->resultado);
        $contexto = [...$this->referencias,
            'evento' => 'fluxo.execucao', 'fluxo' => $this->fluxo,
            'resultado' => $this->resultado, 'motivo' => $this->motivo,
            'duracao_ms' => round((hrtime(true) - $this->inicio) / 1_000_000, 2),
            'etapas' => array_values($this->etapas), 'metricas' => $this->metricas,
        ];
        $nivel = match ($this->resultado) {
            'falha_definitiva' => 'error',
            'impedido', 'retentativa', 'falha_execucao' => 'warning',
            default => 'info',
        };
        $registrar = fn () => Log::channel($this->canal)->log($nivel, 'fluxo.execucao', ContextoOperacional::selecionar($contexto));
        if ($nivel === 'info' && DB::transactionLevel() > 0) {
            DB::afterCommit($registrar);
        } else {
            $registrar();
        }
    }
}

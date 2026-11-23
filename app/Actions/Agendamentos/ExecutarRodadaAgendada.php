<?php

namespace App\Actions\Agendamentos;

use App\Support\ProcessosAgendados;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\Console\Command\Command;
use Throwable;

class ExecutarRodadaAgendada
{
    private const ETAPAS = [
        'certificados:enviar-pendente',
        'interlab:enviar-certificado-pendente',
        'app:processar-senhas-interlab-confirmado',
        'avaliacoes:enviar-lembrete-proc-laboratorio',
        'avaliacoes:enviar-lembrete-proposta-acoes',
        'avaliacoes:enviar-lembrete-acoes-corretivas',
        'cursos:emails-automaticos',
    ];

    /** @param callable(string): int $executarComando */
    public function execute(CarbonImmutable $dia, callable $executarComando, bool $retomar = false): int
    {
        $data = $dia->toDateString();

        $resultado = Cache::lock("rodada-agendada:{$data}", 6 * 3600)->get(
            fn (): int => $this->executarComTrava($data, $executarComando, $retomar),
        );

        if ($resultado === false) {
            ProcessosAgendados::log('rodada_em_execucao', ['data_rodada' => $data], 'warning');

            return $retomar ? Command::FAILURE : Command::SUCCESS;
        }

        return $resultado;
    }

    /** @param callable(string): int $executarComando */
    private function executarComTrava(string $data, callable $executarComando, bool $retomar): int
    {
        $rodadaId = (string) Str::uuid();
        $etapas = array_fill_keys(self::ETAPAS, ['status' => 'pendente']);

        if ($retomar) {
            $rodada = DB::table('rodadas_agendadas')
                ->where('data', $data)
                ->where('status', 'em_execucao')
                ->first();

            if ($rodada === null) {
                return Command::FAILURE;
            }

            $rodadaId = $rodada->id;
            $etapas = json_decode($rodada->etapas, true, 512, JSON_THROW_ON_ERROR);
        } elseif (! DB::table('rodadas_agendadas')->insertOrIgnore([
            'id' => $rodadaId,
            'data' => $data,
            'status' => 'em_execucao',
            'etapas' => json_encode($etapas, JSON_THROW_ON_ERROR),
            'iniciada_em' => now(),
        ])) {
            ProcessosAgendados::log('rodada_ja_iniciada', ['data_rodada' => $data]);

            return Command::SUCCESS;
        }

        return ProcessosAgendados::executar([
            'rodada_id' => $rodadaId,
            'data_rodada' => $data,
        ], function () use ($rodadaId, $etapas, $executarComando, $retomar): int {
            $inicio = hrtime(true);
            ProcessosAgendados::log($retomar ? 'rodada_retomada' : 'rodada_iniciada');
            $falhas = 0;

            foreach (self::ETAPAS as $comando) {
                $status = $etapas[$comando]['status'];

                if ($status === 'em_execucao') {
                    $etapas[$comando]['status'] = 'interrompida';
                    $etapas[$comando]['concluida_em'] = now()->toIso8601String();
                    $this->atualizarEtapas($rodadaId, $etapas);
                    ProcessosAgendados::log('etapa_interrompida', ['etapa' => $comando], 'error');
                    $falhas++;

                    continue;
                }

                if ($status !== 'pendente') {
                    $falhas += (int) in_array($status, ['falhou', 'interrompida'], true);

                    continue;
                }

                $etapas[$comando] = ['status' => 'em_execucao', 'iniciada_em' => now()->toIso8601String()];
                $this->atualizarEtapas($rodadaId, $etapas);

                $codigo = ProcessosAgendados::executar(['etapa' => $comando], function () use ($executarComando, $comando): int {
                    try {
                        return $executarComando($comando);
                    } catch (Throwable $exception) {
                        ProcessosAgendados::log('etapa_interrompida', ['exception_class' => $exception::class], 'error');

                        return Command::FAILURE;
                    }
                });

                $sucesso = $codigo === Command::SUCCESS;
                $falhas += (int) ! $sucesso;
                $etapas[$comando] = array_replace($etapas[$comando], [
                    'status' => $sucesso ? 'concluida' : 'falhou',
                    'concluida_em' => now()->toIso8601String(),
                    'codigo_saida' => $codigo,
                ]);
                $this->atualizarEtapas($rodadaId, $etapas);
            }

            DB::table('rodadas_agendadas')->where('id', $rodadaId)->update([
                'status' => $falhas > 0 ? 'concluida_com_falhas' : 'concluida',
                'concluida_em' => now(),
            ]);

            ProcessosAgendados::log('rodada_concluida', [
                'etapas' => count(self::ETAPAS),
                'etapas_com_falha' => $falhas,
                'duracao_ms' => (int) ((hrtime(true) - $inicio) / 1_000_000),
            ], $falhas > 0 ? 'warning' : 'info');

            return $falhas > 0 ? Command::FAILURE : Command::SUCCESS;
        });
    }

    public function verificar(CarbonImmutable $dia): bool
    {
        $rodada = DB::table('rodadas_agendadas')
            ->where('data', $dia->toDateString())
            ->where('status', 'em_execucao')
            ->first(['id', 'data', 'etapas']);

        if ($rodada === null) {
            return false;
        }

        ProcessosAgendados::log('rodada_ainda_em_execucao', [
            'rodada_id' => $rodada->id,
            'data_rodada' => $rodada->data,
            'etapas' => json_decode($rodada->etapas, true, 512, JSON_THROW_ON_ERROR),
        ], 'warning');

        return true;
    }

    private function atualizarEtapas(string $rodadaId, array $etapas): void
    {
        DB::table('rodadas_agendadas')->where('id', $rodadaId)->update([
            'etapas' => json_encode($etapas, JSON_THROW_ON_ERROR),
        ]);
    }
}

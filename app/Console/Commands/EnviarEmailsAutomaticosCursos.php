<?php

namespace App\Console\Commands;

use App\Actions\CoordenarCacheEmailsCursos;
use App\Actions\EnfileirarConferenciaCursoAction;
use App\Actions\EnfileirarEmailInscritoCursoAction;
use App\Actions\EnfileirarPendenciasCursoAction;
use App\Models\AgendaCursos;
use App\Support\Observabilidade\ResumoExecucao;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class EnviarEmailsAutomaticosCursos extends Command
{
    public const SEM_EMAIL_PENDENTE_CACHE_KEY = 'cursos:emails-automaticos:sem-email-pendente';

    protected $signature = 'cursos:emails-automaticos';

    protected $description = 'Enfileira um aviso ou material de curso online ou IN-COMPANY por execução';

    public function handle(
        EnfileirarConferenciaCursoAction $enfileirarConferencia,
        EnfileirarPendenciasCursoAction $enfileirarPendencias,
        EnfileirarEmailInscritoCursoAction $enfileirarInscrito,
    ): int {
        $resumo = new ResumoExecucao('cursos.emails_automaticos');

        return $resumo->executar(function ($resumo) use ($enfileirarConferencia, $enfileirarPendencias, $enfileirarInscrito): int {
            if (Cache::has(self::SEM_EMAIL_PENDENTE_CACHE_KEY)) {
                $resumo->resultado('ignorado', 'cache_sem_pendencias');

                return self::SUCCESS;
            }

            $coordenador = app(CoordenarCacheEmailsCursos::class);
            $versao = $coordenador->versao();
            [$inicioCiclo, $dataConferencia, $dataParticipantes] = $this->periodoAtual();
            $falhou = false;

            if ($this->processarConferencias($enfileirarConferencia, $dataConferencia, $falhou, resumo: $resumo)) {
                $resumo->resultado($falhou ? 'falha_definitiva' : 'enfileirado');

                return $falhou ? self::FAILURE : self::SUCCESS;
            }

            if ($this->processarPendencias($enfileirarPendencias, $dataParticipantes, $falhou, resumo: $resumo)) {
                $resumo->resultado($falhou ? 'falha_definitiva' : 'enfileirado');

                return $falhou ? self::FAILURE : self::SUCCESS;
            }

            if ($this->processarParticipantes($enfileirarInscrito, $dataParticipantes, $falhou, resumo: $resumo)) {
                $resumo->resultado($falhou ? 'falha_definitiva' : 'enfileirado');

                return $falhou ? self::FAILURE : self::SUCCESS;
            }

            if ($falhou) {
                $resumo->resultado('falha_definitiva', 'execucao_com_falhas');

                return self::FAILURE;
            }

            $expiraEm = $inicioCiclo->addDay();

            if ($coordenador->registrarEsgotamento($versao, $expiraEm)) {
                $resumo->resultado('ignorado', 'sem_pendencias');
            }

            if (Cache::has(self::SEM_EMAIL_PENDENTE_CACHE_KEY)) {
                $this->info('Nenhum e-mail automático de curso apto para enfileiramento. Consultas suspensas até as 06h seguintes.');
            }

            return self::SUCCESS;
        });
    }

    /**
     * @return array{0: CarbonImmutable, 1: string, 2: string}
     */
    private function periodoAtual(): array
    {

        $agora = CarbonImmutable::now('America/Sao_Paulo');
        $inicioCiclo = $agora->hour < 6
            ? $agora->subDay()->startOfDay()->setTime(6, 0)
            : $agora->startOfDay()->setTime(6, 0);

        return [
            $inicioCiclo,
            $inicioCiclo->addDays(2)->toDateString(),
            $inicioCiclo->addDay()->toDateString(),
        ];
    }

    private function processarConferencias(EnfileirarConferenciaCursoAction $action, string $data, bool &$falhou, ResumoExecucao $resumo): bool
    {
        $agendas = AgendaCursos::query()
            ->with(['curso', 'materiaisTreinamento'])
            ->where('status', 'CONFIRMADO')
            ->whereIn('tipo_agendamento', ['ONLINE', 'IN-COMPANY'])
            ->whereDate('data_inicio', $data)
            ->whereNull('conferencia_enviada_em')
            ->orderBy('id')
            ->lazyById(100);

        foreach ($agendas as $agenda) {
            if ($this->tentar(
                fn (): bool => $action->execute($agenda->getKey(), $data, resumo: $resumo),
                'conferencia',
                $agenda->getKey(),
                null,
                $data,
                $falhou, resumo: $resumo)) {
                $this->info("Aviso de conferência da agenda {$agenda->getKey()} enfileirado.");

                return true;
            }
        }

        return false;
    }

    private function processarPendencias(EnfileirarPendenciasCursoAction $action, string $data, bool &$falhou, ResumoExecucao $resumo): bool
    {
        $agendas = AgendaCursos::query()
            ->with(['curso', 'materiaisTreinamento'])
            ->where('status', 'CONFIRMADO')
            ->whereIn('tipo_agendamento', ['ONLINE', 'IN-COMPANY'])
            ->whereDate('data_inicio', $data)
            ->whereNull('pendencias_enviadas_em')
            ->whereNull('informacoes_enviadas_em')
            ->where(function ($query): void {
                $query->whereNull('link_aula')
                    ->orWhere('link_aula', '')
                    ->orWhereDoesntHave('materiaisTreinamento');
            })
            ->orderBy('id')
            ->lazyById(100);

        foreach ($agendas as $agenda) {
            if ($this->conciliarConfirmacao($agenda->getKey(), $falhou, resumo: $resumo)) {
                continue;
            }

            if ($this->tentar(
                fn (): bool => $action->execute($agenda->getKey(), $data, resumo: $resumo),
                'pendencias',
                $agenda->getKey(),
                null,
                $data,
                $falhou, resumo: $resumo)) {
                $this->info("Aviso de pendências da agenda {$agenda->getKey()} enfileirado.");

                return true;
            }
        }

        return false;
    }

    private function processarParticipantes(EnfileirarEmailInscritoCursoAction $action, string $data, bool &$falhou, ResumoExecucao $resumo): bool
    {
        $agendas = AgendaCursos::query()
            ->with(['curso', 'materiaisTreinamento'])
            ->where('status', 'CONFIRMADO')
            ->whereIn('tipo_agendamento', ['ONLINE', 'IN-COMPANY'])
            ->whereDate('data_inicio', $data)
            ->whereNull('informacoes_enviadas_em')
            ->whereNull('pendencias_enviadas_em')
            ->whereNotNull('link_aula')
            ->where('link_aula', '!=', '')
            ->whereHas('materiaisTreinamento')
            ->orderBy('id')
            ->lazyById(100);

        foreach ($agendas as $agenda) {
            if (! $this->conciliarConfirmacao($agenda->getKey(), $falhou, resumo: $resumo)) {
                $cursor = null;
                do {
                    $inscritos = $agenda->inscritos()
                        ->whereNull('informacoes_enviadas_em')
                        ->orderBy('data_inscricao')
                        ->orderBy('id')
                        ->cursorPaginate(100, ['*'], 'cursor', $cursor);

                    foreach ($inscritos as $inscrito) {
                        if ($this->tentar(
                            fn (): bool => $action->execute($agenda->getKey(), $inscrito->getKey(), $data, resumo: $resumo),
                            'participante',
                            $agenda->getKey(),
                            $inscrito->getKey(),
                            $data,
                            $falhou, resumo: $resumo)) {
                            $this->info("Informações da inscrição {$inscrito->getKey()} enfileiradas.");

                            return true;
                        }
                    }
                    $cursor = $inscritos->nextCursor();
                } while ($cursor !== null);
            }
        }

        return false;
    }

    private function conciliarConfirmacao(int $agendaCursoId, bool &$falhou, ResumoExecucao $resumo): bool
    {
        try {
            return DB::transaction(function () use ($agendaCursoId): bool {
                $agenda = AgendaCursos::query()->lockForUpdate()->find($agendaCursoId);

                if (! $agenda) {
                    return false;
                }

                $agenda->sincronizarConfirmacaoInformacoes();

                return $agenda->informacoes_enviadas_em !== null;
            });
        } catch (Throwable $exception) {
            $falhou = true;
            $this->registrarFalha('confirmacao', $agendaCursoId, null, null, null, $exception, resumo: $resumo);

            return false;
        }
    }

    private function tentar(
        callable $acao,
        string $tipo,
        int $agendaCursoId,
        ?int $inscritoId,
        ?string $data,
        bool &$falhou, ResumoExecucao $resumo): bool
    {
        try {
            return $acao();
        } catch (Throwable $exception) {
            $falhou = true;
            $this->registrarFalha($tipo, $agendaCursoId, $inscritoId, $data, null, $exception, resumo: $resumo);

            return false;
        }
    }

    private function registrarFalha(
        string $tipo,
        int $agendaCursoId,
        ?int $inscritoId,
        ?string $data,
        ?string $tentativaId,
        Throwable $exception, ResumoExecucao $resumo): void
    {
        $resumo->contexto(['tipo' => $tipo, 'agenda_curso_id' => $agendaCursoId, 'inscrito_id' => $inscritoId, 'tentativa_id' => $tentativaId]);
        report($exception);
        $resumo->incrementar('falhas');
        $resumo->resultado('falha_definitiva', 'erro_tecnico');
    }
}

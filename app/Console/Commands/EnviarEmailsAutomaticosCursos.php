<?php

namespace App\Console\Commands;

use App\Actions\EnfileirarConferenciaCursoAction;
use App\Actions\EnfileirarEmailInscritoCursoAction;
use App\Actions\EnfileirarPendenciasCursoAction;
use App\Models\AgendaCursos;
use App\Support\ProcessosAgendados;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class EnviarEmailsAutomaticosCursos extends Command
{
    protected $signature = 'cursos:emails-automaticos';

    protected $description = 'Enfileira avisos e materiais pendentes de cursos online ou IN-COMPANY';

    public function handle(
        EnfileirarConferenciaCursoAction $enfileirarConferencia,
        EnfileirarPendenciasCursoAction $enfileirarPendencias,
        EnfileirarEmailInscritoCursoAction $enfileirarInscrito,
    ): int {
        return ProcessosAgendados::execucao($this->getName(), function () use ($enfileirarConferencia, $enfileirarPendencias, $enfileirarInscrito): int {
            $dia = ProcessosAgendados::dataReferencia();
            $dataConferencia = $dia->addDays(2)->toDateString();
            $dataParticipantes = $dia->addDay()->toDateString();
            $falhou = false;

            $conferencias = $this->processarConferencias($enfileirarConferencia, $dataConferencia, $falhou);
            $pendencias = $this->processarPendencias($enfileirarPendencias, $dataParticipantes, $falhou);
            $participantes = $this->processarParticipantes($enfileirarInscrito, $dataParticipantes, $falhou);

            ProcessosAgendados::resumo([
                'data_conferencia' => $dataConferencia,
                'data_participantes' => $dataParticipantes,
                'conferencias_enfileiradas' => $conferencias,
                'pendencias_enfileiradas' => $pendencias,
                'participantes_enfileirados' => $participantes,
            ]);

            if (! $falhou && $conferencias + $pendencias + $participantes === 0) {
                ProcessosAgendados::log('emails_cursos_sem_pendencias', [
                    'data_conferencia' => $dataConferencia,
                    'data_participantes' => $dataParticipantes,
                ]);
                $this->info('Nenhum e-mail automático de curso apto para enfileiramento.');
            } else {
                $this->info("Enfileirados: {$conferencias} conferência(s), {$pendencias} aviso(s) de pendências e {$participantes} envio(s) a inscritos.");
            }

            return $falhou ? self::FAILURE : self::SUCCESS;
        });
    }

    private function processarConferencias(EnfileirarConferenciaCursoAction $action, string $data, bool &$falhou): int
    {
        $enfileirados = 0;
        $agendas = AgendaCursos::query()
            ->where('status', 'CONFIRMADO')
            ->whereIn('tipo_agendamento', ['ONLINE', 'IN-COMPANY'])
            ->whereDate('data_inicio', $data)
            ->whereNull('conferencia_enviada_em')
            ->orderBy('id')
            ->lazyById(100);

        foreach ($agendas as $agenda) {
            if ($this->tentar(
                fn (): bool => $action->execute($agenda->getKey(), $data, agendado: ProcessosAgendados::emRodada()),
                'conferencia',
                $agenda->getKey(),
                null,
                $data,
                $falhou,
            )) {
                $enfileirados++;
            }
        }

        return $enfileirados;
    }

    private function processarPendencias(EnfileirarPendenciasCursoAction $action, string $data, bool &$falhou): int
    {
        $enfileirados = 0;
        $agendas = AgendaCursos::query()
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
            if ($this->conciliarConfirmacao($agenda->getKey(), $falhou)) {
                continue;
            }

            if ($this->tentar(
                fn (): bool => $action->execute($agenda->getKey(), $data, agendado: ProcessosAgendados::emRodada()),
                'pendencias',
                $agenda->getKey(),
                null,
                $data,
                $falhou,
            )) {
                $enfileirados++;
            }
        }

        return $enfileirados;
    }

    private function processarParticipantes(EnfileirarEmailInscritoCursoAction $action, string $data, bool &$falhou): int
    {
        $enfileirados = 0;
        $agendas = AgendaCursos::query()
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
            if (! $this->conciliarConfirmacao($agenda->getKey(), $falhou)) {
                $cursor = null;
                do {
                    $inscritos = $agenda->inscritos()
                        ->whereNull('informacoes_enviadas_em')
                        ->orderBy('data_inscricao')
                        ->orderBy('id')
                        ->cursorPaginate(100, ['*'], 'cursor', $cursor);

                    foreach ($inscritos as $inscrito) {
                        if ($this->tentar(
                            fn (): bool => $action->execute($agenda->getKey(), $inscrito->getKey(), $data, agendado: ProcessosAgendados::emRodada()),
                            'participante',
                            $agenda->getKey(),
                            $inscrito->getKey(),
                            $data,
                            $falhou,
                        )) {
                            $enfileirados++;
                        }
                    }
                    $cursor = $inscritos->nextCursor();
                } while ($cursor !== null);
            }
        }

        return $enfileirados;
    }

    private function conciliarConfirmacao(int $agendaCursoId, bool &$falhou): bool
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
            $this->registrarFalha('confirmacao', $agendaCursoId, null, null, $exception);

            return false;
        }
    }

    private function tentar(
        callable $acao,
        string $tipo,
        int $agendaCursoId,
        ?int $inscritoId,
        ?string $data,
        bool &$falhou,
    ): bool {
        try {
            return $acao();
        } catch (Throwable $exception) {
            $falhou = true;
            $this->registrarFalha($tipo, $agendaCursoId, $inscritoId, $data, $exception);

            return false;
        }
    }

    private function registrarFalha(
        string $tipo,
        int $agendaCursoId,
        ?int $inscritoId,
        ?string $data,
        Throwable $exception,
    ): void {
        ProcessosAgendados::log('emails_cursos_falha_enfileiramento', [
            'tipo' => $tipo,
            'agenda_curso_id' => $agendaCursoId,
            'inscrito_id' => $inscritoId,
            'data_curso' => $data,
            'exception_class' => $exception::class,
        ], 'error');
    }
}

<?php

namespace App\Actions;

use App\Jobs\EnviarConferenciaCursoJob;
use App\Models\AgendaCursos;
use App\Support\Observabilidade\ResumoExecucao;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EnfileirarConferenciaCursoAction
{
    public const DESTINATARIO_ADMINISTRATIVO = 'administrativo@redemetrologica.com.br';

    public function execute(int $agendaCursoId, string $dataEsperada, ?ResumoExecucao $resumo = null): bool
    {
        $resumo = ResumoExecucao::operacao($resumo, 'EnfileirarConferenciaCursoAction', [
            'tipo' => 'conferencia',
            'agenda_curso_id' => $agendaCursoId,
        ], 'mailsent');
        $resumo->resultado('ignorado', 'inelegivel');

        return $resumo->executar(fn () => $this->executarObservado($agendaCursoId, $dataEsperada, $resumo));
    }

    private function executarObservado(int $agendaCursoId, string $dataEsperada, ResumoExecucao $resumo): bool
    {
        return DB::transaction(function () use ($resumo, $agendaCursoId, $dataEsperada): bool {
            $agenda = $resumo->etapa('carregar_registros', fn () => AgendaCursos::query()
                ->with(['curso', 'materiaisTreinamento'])
                ->lockForUpdate()
                ->find($agendaCursoId));

            if ($resumo->etapa('validar_elegibilidade', fn () => ! $agenda
                || $agenda->status !== 'CONFIRMADO'
                || ! in_array($agenda->tipo_agendamento, ['ONLINE', 'IN-COMPANY'], true)
                || $agenda->data_inicio?->toDateString() !== $dataEsperada
                || $agenda->conferencia_enviada_em !== null
                || $agenda->conferencia_envio_id !== null)) {
                $resumo->resultadoEtapa('validar_elegibilidade', 'ignorado');

                return false;
            }

            $resumo->contexto([
                'agenda_curso_id' => $agenda->getKey(),
                'agenda_uid' => $agenda->uid,
            ]);

            $destinatarioValido = $resumo->etapa('validar_destinatario', function () use ($agenda): bool {
                if (Validator::make(
                    ['email' => self::DESTINATARIO_ADMINISTRATIVO],
                    ['email' => ['required', 'email:rfc']],
                )->passes()) {
                    return true;
                }

                Log::channel('validation')->warning('Destinatário administrativo inválido para aviso de conferência de curso.', [
                    'tipo' => 'conferencia',
                    'agenda_curso_id' => $agenda->getKey(),
                    'agenda_uid' => $agenda->uid,
                ]);

                return false;
            });

            if (! $destinatarioValido) {
                $resumo->resultadoEtapa('validar_destinatario', 'impedido');

                return false;
            }

            [$tentativa, $snapshot] = $resumo->etapa('preparar_mensagem', function () use ($agenda, $dataEsperada): array {
                $tentativa = (string) Str::uuid();
                $snapshot = [
                    'destinatario' => self::DESTINATARIO_ADMINISTRATIVO,
                    'treinamento' => $agenda->curso->descricao,
                    'data_curso' => $dataEsperada,
                    'data_anterior' => CarbonImmutable::parse($dataEsperada, 'America/Sao_Paulo')
                        ->subDay()
                        ->format('d/m/Y'),
                ];

                return [$tentativa, $snapshot];
            });

            $resumo->etapa('confirmar_estado', fn () => $agenda->update(['conferencia_envio_id' => $tentativa]));

            $resumo->etapa('enfileirar', fn () => Bus::dispatch((new EnviarConferenciaCursoJob(
                $agenda->getKey(),
                $tentativa,
                $dataEsperada,
                $snapshot,
            ))->onConnection('database')->beforeCommit()));

            $resumo->contexto([
                'tipo' => 'conferencia',
                'agenda_curso_id' => $agenda->getKey(),
                'agenda_uid' => $agenda->uid,
                'tentativa_id' => $tentativa,
            ]);
            $resumo->resultado('enfileirado');
            $resumo->incrementar('enfileirados');

            return true;
        });
    }
}

<?php

namespace App\Actions;

use App\Jobs\EnviarPendenciasCursoJob;
use App\Models\AgendaCursos;
use App\Support\Observabilidade\ResumoExecucao;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EnfileirarPendenciasCursoAction
{
    public const DESTINATARIO_ADMINISTRATIVO = 'administrativo@redemetrologica.com.br';

    public function execute(int $agendaCursoId, string $dataEsperada, ?ResumoExecucao $resumo = null): bool
    {
        $resumo = ResumoExecucao::operacao($resumo, 'EnfileirarPendenciasCursoAction', [
            'tipo' => 'pendencias',
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
                || $agenda->informacoes_enviadas_em !== null
                || $agenda->pendencias_enviadas_em !== null
                || $agenda->pendencias_envio_id !== null
                || (filled($agenda->link_aula) && $agenda->materiaisTreinamento->isNotEmpty()))) {
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

                Log::channel('validation')->warning('Destinatário administrativo inválido para aviso de pendências de curso.', [
                    'tipo' => 'pendencias',
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
                ];

                return [$tentativa, $snapshot];
            });

            $resumo->etapa('confirmar_estado', fn () => $agenda->update(['pendencias_envio_id' => $tentativa]));

            $resumo->etapa('enfileirar', fn () => Bus::dispatch((new EnviarPendenciasCursoJob(
                $agenda->getKey(),
                $tentativa,
                $dataEsperada,
                $snapshot,
            ))->onConnection('database')->beforeCommit()));

            $resumo->contexto([
                'tipo' => 'pendencias',
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

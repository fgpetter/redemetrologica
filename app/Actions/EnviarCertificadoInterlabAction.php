<?php

namespace App\Actions;

use App\Jobs\GerarCertificadoInterlabJob;
use App\Models\DadosGeraDoc;
use App\Models\InterlabInscrito;
use App\Support\Observabilidade\ResumoExecucao;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use LogicException;

class EnviarCertificadoInterlabAction
{
    /**
     * Cria registro de log e dispara e-mail com link do certificado
     */
    public function execute(InterlabInscrito $inscrito, ?string $email = null, int $delay = 0, ?ResumoExecucao $resumo = null): DadosGeraDoc
    {
        $resumo = ResumoExecucao::operacao($resumo, 'EnviarCertificadoInterlabAction', [
            'tipo' => 'certificado_interlab',
            'agenda_id' => $inscrito->agenda_interlab_id,
            'inscrito_id' => $inscrito->id,
            'inscrito_uid' => $inscrito->uid,
        ], 'mailsent');
        $resumo->resultado('ignorado', 'inelegivel');

        return $resumo->executar(fn () => $this->executarObservado($inscrito, $email, $delay, $resumo));
    }

    private function executarObservado(InterlabInscrito $inscrito, ?string $email, int $delay, ResumoExecucao $resumo): DadosGeraDoc
    {
        return DB::transaction(function () use ($resumo, $inscrito, $email, $delay): DadosGeraDoc {
            $inscrito = $resumo->etapa('carregar_registros', function () use ($inscrito): InterlabInscrito {
                $inscrito = InterlabInscrito::query()->lockForUpdate()->findOrFail($inscrito->id);

                return $inscrito->load(['laboratorio', 'agendaInterlab.interlab']);
            });
            $emailCertificado = $resumo->etapa('validar_destinatario', function () use ($inscrito, $email): string {
                $emailCertificado = trim((string) ($email ?? $inscrito->email ?? ''));
                $validator = Validator::make(['email' => $emailCertificado], ['email' => ['required', 'email:rfc']]);

                if ($validator->fails()) {
                    Log::channel('validation')->warning('certificado.validacao', ['campos' => array_keys($validator->errors()->toArray())]);
                    Log::channel('certificados_falha')->warning('Contato inválido ao solicitar certificado Interlab manualmente.', [
                        'evento' => 'certificado.contato_invalido',
                        'resultado' => 'impedido',
                        'motivo' => 'contato_invalido',
                        'tipo' => 'certificado_interlab',
                        'agenda_id' => $inscrito->agenda_interlab_id,
                        'inscrito_id' => $inscrito->id,
                        'inscrito_uid' => $inscrito->uid,
                    ]);

                    $validator->validate();
                }

                return $emailCertificado;
            });

            $resumo->etapa('validar_conexao_fila', function (): void {
                if ((config('queue.connections.database.connection') ?? config('database.default')) !== DB::connection()->getName()) {
                    throw new LogicException('O envio de certificados exige fila e inscrição na mesma conexão de banco.');
                }
            });

            $dadosDoc = $resumo->etapa('preparar_documento', fn () => DadosGeraDoc::create([
                'content' => [
                    'participante_id' => $inscrito->id,
                    'laboratorio_nome' => $inscrito->laboratorio->nome ?? 'Laboratório',
                    'laboratorio_email' => $emailCertificado,
                    'interlab_nome' => $inscrito->agendaInterlab->interlab->nome,
                    'interlab_data' => $inscrito->agendaInterlab->data_inicio?->format('d/m/Y'),
                ],
                'tipo' => 'certificado_interlab',
            ]));

            $resumo->contexto([
                'tipo' => 'certificado_interlab',
                'agenda_id' => $inscrito->agenda_interlab_id,
                'inscrito_id' => $inscrito->id,
                'inscrito_uid' => $inscrito->uid,
                'documento_id' => $dadosDoc->id,
            ]);

            $resumo->etapa('confirmar_estado', fn () => $inscrito->update([
                'certificado_emitido' => now(),
                'certificado_envio_id' => null,
                'certificado_path' => $dadosDoc->storage_path,
            ]));

            $resumo->etapa('enfileirar', fn () => Bus::dispatch((new GerarCertificadoInterlabJob($dadosDoc->id))
                ->onConnection('database')->delay(now()->addSeconds($delay))->beforeCommit()));

            $resumo->resultado('enfileirado');
            $resumo->incrementar('enfileirados');

            return $dadosDoc;
        });
    }
}

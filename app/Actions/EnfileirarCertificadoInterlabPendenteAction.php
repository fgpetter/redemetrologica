<?php

namespace App\Actions;

use App\Jobs\EnviarCertificadoInterlabPendenteJob;
use App\Models\DadosGeraDoc;
use App\Models\InterlabInscrito;
use App\Support\Observabilidade\ResumoExecucao;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use LogicException;

class EnfileirarCertificadoInterlabPendenteAction
{
    public function execute(int $inscritoId, ?ResumoExecucao $resumo = null): bool
    {
        $resumo = ResumoExecucao::operacao($resumo, 'EnfileirarCertificadoInterlabPendenteAction', [
            'tipo' => 'certificado_interlab',
            'inscrito_id' => $inscritoId,
        ], 'mailsent');
        $resumo->resultado('ignorado', 'inelegivel');

        return $resumo->executar(fn () => $this->executarObservado($inscritoId, $resumo));
    }

    private function executarObservado(int $inscritoId, ResumoExecucao $resumo): bool
    {
        return DB::transaction(function () use ($resumo, $inscritoId): bool {
            $inscrito = $resumo->etapa('carregar_registros', fn () => InterlabInscrito::certificadosPendentes()
                ->with(['pessoa', 'laboratorio', 'agendaInterlab.interlab'])
                ->lockForUpdate()
                ->find($inscritoId));

            if (! $inscrito) {
                $resumo->resultadoEtapa('carregar_registros', 'ignorado');

                return false;
            }

            $resumo->contexto([
                'agenda_id' => $inscrito->agenda_interlab_id,
                'inscrito_uid' => $inscrito->uid,
            ]);

            $destinatarios = $resumo->etapa('validar_destinatario', function () use ($inscrito): ?array {
                $tecnico = trim($inscrito->email ?? '');
                $pessoa = trim($inscrito->pessoa?->email ?? '');
                $tecnicoValido = $this->emailValido($tecnico);
                $pessoaValida = $this->emailValido($pessoa);

                if (! $tecnicoValido || ! $pessoaValida) {
                    Log::channel('validation')->warning('certificado.validacao', ['responsavel_tecnico_valido' => $tecnicoValido, 'pessoa_valida' => $pessoaValida]);
                    Log::channel('certificados_falha')->warning('Contato de certificado ausente ou inválido.', [
                        'evento' => 'certificado.contato_invalido',
                        'resultado' => 'impedido',
                        'motivo' => 'contato_invalido',
                        'tipo' => 'certificado_interlab',
                        'agenda_id' => $inscrito->agenda_interlab_id,
                        'inscrito_id' => $inscrito->id,
                        'inscrito_uid' => $inscrito->uid,
                        'responsavel_tecnico_valido' => $tecnicoValido,
                        'pessoa_valida' => $pessoaValida,
                    ]);
                }

                if (! $tecnicoValido && ! $pessoaValida) {
                    return null;
                }

                $to = $tecnicoValido ? $tecnico : $pessoa;
                $cc = $tecnicoValido && $pessoaValida && strcasecmp($tecnico, $pessoa) !== 0 ? $pessoa : null;

                return [$to, $cc];
            });

            if ($destinatarios === null) {
                $resumo->resultadoEtapa('validar_destinatario', 'impedido');

                return false;
            }

            [$to, $cc] = $destinatarios;

            $resumo->etapa('validar_conexao_fila', function (): void {
                $connection = DB::connection()->getName();

                if ((config('queue.connections.database.connection') ?? config('database.default')) !== $connection) {
                    throw new LogicException('O envio de certificados exige fila e inscrição na mesma conexão de banco.');
                }
            });

            $tentativa = (string) Str::uuid();
            $resumo->contexto(['tentativa_id' => $tentativa]);
            $dadosDoc = $resumo->etapa('preparar_documento', fn () => DadosGeraDoc::create([
                'tipo' => 'certificado_interlab',
                'content' => [
                    'participante_id' => $inscrito->id,
                    'laboratorio_nome' => $inscrito->laboratorio?->nome ?? 'Laboratório',
                    'laboratorio_email' => $to,
                    'interlab_nome' => $inscrito->agendaInterlab->interlab->nome,
                    'interlab_data' => $inscrito->agendaInterlab->data_inicio?->format('d/m/Y'),
                ],
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
                'certificado_envio_id' => $tentativa,
                'certificado_path' => $dadosDoc->storage_path,
            ]));

            $resumo->etapa('enfileirar', fn () => Bus::dispatch((new EnviarCertificadoInterlabPendenteJob(
                $inscrito->id, $dadosDoc->id, $tentativa, $to, $cc,
            ))->onConnection('database')->beforeCommit()));

            $resumo->resultado('enfileirado');
            $resumo->incrementar('enfileirados');

            return true;
        });
    }

    private function emailValido(string $email): bool
    {
        return Validator::make(['email' => $email], ['email' => ['required', 'email:rfc']])->passes();
    }
}

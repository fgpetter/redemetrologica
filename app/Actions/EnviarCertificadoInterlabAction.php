<?php

namespace App\Actions;

use App\Jobs\GerarCertificadoInterlabJob;
use App\Models\DadosGeraDoc;
use App\Models\InterlabInscrito;
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
    public function execute(InterlabInscrito $inscrito, ?string $email = null, int $delay = 0): DadosGeraDoc
    {
        return DB::transaction(function () use ($inscrito, $email, $delay): DadosGeraDoc {
            $inscrito = InterlabInscrito::query()->lockForUpdate()->findOrFail($inscrito->id);
            $inscrito->load(['laboratorio', 'agendaInterlab.interlab']);
            $emailCertificado = trim((string) ($email ?? $inscrito->email ?? ''));
            $validator = Validator::make(['email' => $emailCertificado], ['email' => ['required', 'email:rfc']]);

            if ($validator->fails()) {
                Log::channel('certificados_falha')->warning('Contato inválido ao solicitar certificado Interlab manualmente.', [
                    'tipo' => 'certificado_interlab',
                    'agenda_id' => $inscrito->agenda_interlab_id,
                    'inscrito_id' => $inscrito->id,
                ]);

                $validator->validate();
            }

            if ((config('queue.connections.database.connection') ?? config('database.default')) !== DB::connection()->getName()) {
                throw new LogicException('O envio de certificados exige fila e inscrição na mesma conexão de banco.');
            }

            $dadosDoc = DadosGeraDoc::create([
                'content' => [
                    'participante_id' => $inscrito->id,
                    'laboratorio_nome' => $inscrito->laboratorio->nome ?? 'Laboratório',
                    'laboratorio_email' => $emailCertificado,
                    'interlab_nome' => $inscrito->agendaInterlab->interlab->nome,
                    'interlab_data' => $inscrito->agendaInterlab->data_inicio?->format('d/m/Y'),
                ],
                'tipo' => 'certificado_interlab',
            ]);

            $inscrito->update([
                'certificado_emitido' => now(),
                'certificado_envio_id' => null,
                'certificado_path' => $dadosDoc->storage_path,
            ]);

            Bus::dispatch((new GerarCertificadoInterlabJob($dadosDoc->id))
                ->onConnection('database')->delay(now()->addSeconds($delay))->beforeCommit());

            Log::channel('mailsent')->info('Certificado Interlab enfileirado manualmente.', [
                'tipo' => 'certificado_interlab',
                'agenda_id' => $inscrito->agenda_interlab_id,
                'inscrito_id' => $inscrito->id,
                'documento_id' => $dadosDoc->id,
            ]);

            return $dadosDoc;
        });
    }
}

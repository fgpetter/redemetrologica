<?php

namespace App\Actions;

use App\Jobs\EnviarLinkCertificadoJob;
use App\Models\CursoInscrito;
use App\Models\DadosGeraDoc;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use LogicException;

class EnviarCertificadoAction
{
    /**
     * Cria registro de log e dispara e-mail com link do certificado
     */
    public function execute(CursoInscrito $inscrito, int $delay = 0): DadosGeraDoc
    {
        return DB::transaction(function () use ($inscrito, $delay): DadosGeraDoc {
            $inscrito = CursoInscrito::query()->lockForUpdate()->findOrFail($inscrito->id);
            $email = trim((string) ($inscrito->email ?? ''));
            $validator = Validator::make(['email' => $email], ['email' => ['required', 'email:rfc']]);

            if ($validator->fails()) {
                Log::channel('certificados_falha')->warning('Contato de certificado de curso ausente ou inválido.', [
                    'tipo' => 'certificado_curso',
                    'agenda_id' => $inscrito->agenda_curso_id,
                    'inscrito_id' => $inscrito->id,
                ]);

                $validator->validate();
            }

            $conexao = DB::connection()->getName();

            if ((config('queue.connections.database.connection') ?? config('database.default')) !== $conexao) {
                throw new LogicException('O envio de certificados exige fila e inscrição na mesma conexão de banco.');
            }

            $dadosDoc = $this->criarDadosDoc($inscrito, $email);
            $tentativa = (string) Str::uuid();

            $inscrito->update([
                'certificado_emitido' => now(),
                'certificado_envio_id' => $tentativa,
                'certificado_path' => $dadosDoc->storage_path,
            ]);

            Bus::dispatch((new EnviarLinkCertificadoJob($dadosDoc->id, $inscrito->id, $tentativa, $email))
                ->onConnection('database')
                ->delay(now()->addSeconds($delay))
                ->beforeCommit());

            Log::channel('mailsent')->info('Certificado de curso enfileirado.', [
                'tipo' => 'certificado_curso',
                'agenda_id' => $inscrito->agenda_curso_id,
                'inscrito_id' => $inscrito->id,
                'documento_id' => $dadosDoc->id,
                'tentativa_id' => $tentativa,
            ]);

            return $dadosDoc;
        });
    }

    public function executePendente(int $inscritoId): bool
    {
        return DB::transaction(function () use ($inscritoId): bool {
            $inscrito = CursoInscrito::certificadosPendentes()->lockForUpdate()->find($inscritoId);

            if (! $inscrito) {
                return false;
            }

            $this->execute($inscrito);

            return true;
        });
    }

    /**
     * Cria o registro com os dados necessários para gerar o PDF do certificado
     */
    public function criarDadosDoc(CursoInscrito $inscrito, ?string $email = null): DadosGeraDoc
    {
        $inscrito->load(['agendaCurso.curso', 'agendaCurso.instrutor.pessoa', 'empresa']);

        return DadosGeraDoc::create([
            'content' => [
                'participante_id' => $inscrito->id,
                'participante_nome' => $inscrito->nome,
                'participante_email' => $email ?? $inscrito->email,
                'curso_nome' => $inscrito->agendaCurso->curso->descricao,
                'curso_data' => $this->gerarDataFormatada($inscrito->agendaCurso->data_inicio, $inscrito->agendaCurso->data_fim),
                'conteudo_programatico' => $inscrito->agendaCurso->curso->conteudo_programatico,
                'carga_horaria' => $inscrito->agendaCurso->carga_horaria ?? $inscrito->agendaCurso->curso->carga_horaria,
                'instrutor_nome' => $inscrito->agendaCurso->instrutor?->pessoa?->nome_razao,
                'empresa_nome' => $inscrito->empresa->nome_razao ?? null,
            ],
            'tipo' => 'certificado',
        ]);
    }

    private function gerarDataFormatada(\Carbon\Carbon $dataInicio, \Carbon\Carbon $dataFim): string
    {
        if ($dataInicio->format('d/m/Y') !== $dataFim->format('d/m/Y')) {

            if ($dataInicio->diffInDays($dataFim) > 1) {
                // Realizado nos dias: 10/03/2026 a 12/03/2026
                return 'Realizado nos dias: '.$dataInicio->format('d/m/Y').' a '.$dataFim->format('d/m/Y');
            }

            // Realizado nos dias: 10 e 11/03/2026
            return 'Realizado nos dias: '.$dataInicio->format('d').' e '.$dataFim->format('d/m/Y');
        }

        // Realizado no dia: 10/03/2026
        return 'Realizado no dia: '.$dataInicio->format('d/m/Y');
    }
}

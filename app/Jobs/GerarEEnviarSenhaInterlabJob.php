<?php

namespace App\Jobs;

use App\Exceptions\InvalidEmailException;
use App\Mail\LinkSenhaInterlabNotification;
use App\Models\DadosGeraDoc;
use App\Models\InterlabInscrito;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class GerarEEnviarSenhaInterlabJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(public int $inscritoId) {}

    public function handle(): void
    {
        $inscrito = InterlabInscrito::query()
            ->with(['laboratorio', 'empresa', 'agendaInterlab.interlab', 'pessoa'])
            ->find($this->inscritoId);

        if (! $inscrito) {
            Log::warning('GerarEEnviarSenhaInterlabJob: inscrito não encontrado.', [
                'inscrito_id' => $this->inscritoId,
            ]);

            return;
        }

        if ($inscrito->senha_enviada !== null) {
            return;
        }

        if ($inscrito->agendaInterlab?->status !== 'CONFIRMADO') {
            return;
        }

        $interlab = $inscrito->agendaInterlab?->interlab;

        if (empty($interlab?->tag)) {
            throw new \RuntimeException(
                'Tag do interlab não encontrada para inscrito ID: '.$this->inscritoId
            );
        }

        $content = [
            'participante_id' => $inscrito->id,
            'tag_senha' => $inscrito->tag_senha,
            'informacoes_inscricao' => $inscrito->informacoes_inscricao,
            'laboratorio_nome' => $inscrito->laboratorio->nome,
            'laboratorio_email' => $inscrito->email,
            'empresa_nome_razao' => $inscrito->empresa->nome_razao,
            'empresa_cpf_cnpj' => $inscrito->empresa->cpf_cnpj,
            'interlab_nome' => $interlab->nome,
        ];

        $dadosDoc = DadosGeraDoc::create([
            'content' => $content,
            'tipo' => 'tag_senha',
        ]);

        $destinatarios = array_values(array_unique(array_filter(
            [$inscrito->email, $inscrito->pessoa?->email],
            static fn (mixed $email): bool => filled($email)
        )));

        if ($destinatarios === []) {
            new InvalidEmailException([
                'class' => self::class,
                'inscrito_id' => $inscrito->id,
                'inscrito_nome' => $inscrito->nome,
                'inscrito_email' => $inscrito->email,
                'inscrito_pessoa_email' => $inscrito->pessoa?->email,
                'inscrito_pessoa_nome' => $inscrito->pessoa?->nome,
                'inscrito_pessoa_uid' => $inscrito->pessoa?->uid,
            ]);

            return;
        }

        Mail::to($destinatarios)
            ->cc('sistema@redemetrologica.com.br')
            ->sendNow(new LinkSenhaInterlabNotification($dadosDoc));

        $inscrito->update(['senha_enviada' => now()]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Falha ao gerar e enviar senha interlab para inscrito ID: '.$this->inscritoId, [
            'error' => $exception->getMessage(),
        ]);
    }
}

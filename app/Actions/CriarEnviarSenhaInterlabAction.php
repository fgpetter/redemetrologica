<?php

namespace App\Actions;

use App\Mail\LinkSenhaInterlabNotification;
use App\Models\DadosGeraDoc;
use App\Models\InterlabAnalista;
use App\Models\InterlabInscrito;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CriarEnviarSenhaInterlabAction
{
    /**
     * Cria registro de tag senha e agenda envio do e-mail com link para download.
     *
     * @param  InterlabInscrito  $inscrito  Inscrição alvo
     * @param  int  $delaySecs  Atraso em segundos até o envio na fila
     * @param  InterlabAnalista|null  $analista  Quando informado, inclui dados do analista no documento e na tag
     * @return DadosGeraDoc Registro criado (o envio é omitido se não houver e-mails válidos)
     */
    public function execute(
        InterlabInscrito $inscrito,
        int $delaySecs,
        ?InterlabAnalista $analista = null,
    ): DadosGeraDoc {
        $inscrito->loadMissing(['laboratorio', 'empresa', 'agendaInterlab.interlab', 'pessoa']);

        $content = [
            'participante_id' => $inscrito->id,
            'tag_senha' => $analista !== null
                ? ($analista->tag_senha ?? $inscrito->tag_senha)
                : $inscrito->tag_senha,
            'informacoes_inscricao' => $inscrito->informacoes_inscricao,
            'laboratorio_nome' => $inscrito->laboratorio->nome,
            'laboratorio_email' => $inscrito->email,
            'empresa_nome_razao' => $inscrito->empresa->nome_razao,
            'empresa_cpf_cnpj' => $inscrito->empresa->cpf_cnpj,
            'interlab_nome' => $inscrito->agendaInterlab->interlab->nome,
        ];

        if ($analista !== null) {
            $content['analista_nome'] = $analista->nome ?? null;
            $content['analista_email'] = $analista->email ?? null;
        }

        $dadosDoc = DadosGeraDoc::create([
            'content' => $content,
            'tipo' => 'tag_senha',
        ]);

        $destinatarios = array_values(array_unique(array_filter(
            [$inscrito->email, $inscrito->pessoa?->email],
            static fn (mixed $email): bool => filled($email)
        )));

        if ($destinatarios === []) {
            Log::warning('Envio de senha interlab ignorado: nenhum destinatário', [
                'inscrito_id' => $inscrito->id,
                'dados_doc_id' => $dadosDoc->id,
            ]);

            return $dadosDoc;
        }

        Mail::to($destinatarios)
            ->cc('sistema@redemetrologica.com.br')
            ->later(now()->addSeconds($delaySecs), new LinkSenhaInterlabNotification($dadosDoc));

        return $dadosDoc;
    }
}

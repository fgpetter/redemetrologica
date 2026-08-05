<?php

namespace App\Actions;

use App\Exceptions\InvalidEmailException;
use App\Jobs\EnviaSenhaAnalistaJob;
use App\Models\DadosGeraDoc;
use App\Models\InterlabAnalista;
use App\Models\InterlabInscrito;

class CriarEnviarSenhaAnalistaAction
{
    /**
     * Cria registro de Carta Senha do analista e agenda o envio do e-mail com link.
     *
     * Content gravado em DadosGeraDoc (tipo `tag_senha_analista`).
     */
    public function execute(
        InterlabInscrito $inscrito,
        InterlabAnalista $analista,
        int $delaySecs = 0,
    ): DadosGeraDoc {
        $inscrito->loadMissing(['laboratorio', 'empresa', 'agendaInterlab.interlab', 'pessoa']);

        if (empty($analista->tag_senha)) {
            $tagSenha = app(GerarTagSenhaInterlabAction::class)->execute(
                $inscrito->agendaInterlab,
                GerarTagSenhaInterlabAction::TIPO_ANALISTA,
            );
            $analista->update(['tag_senha' => $tagSenha]);
            $analista->refresh();
        }

        /** @var array{
         *     participante_id: int,
         *     analista_id: int,
         *     tag_senha: string|null,
         *     informacoes_inscricao: mixed,
         *     laboratorio_nome: string,
         *     laboratorio_email: string|null,
         *     empresa_nome_razao: string,
         *     empresa_cpf_cnpj: string,
         *     interlab_nome: string,
         *     analista_nome: string|null,
         *     analista_email: string|null
         * } $content
         */
        $content = [
            'participante_id' => $inscrito->id,
            'analista_id' => $analista->id,
            'tag_senha' => $analista->tag_senha,
            'informacoes_inscricao' => $inscrito->informacoes_inscricao,
            'laboratorio_nome' => $inscrito->laboratorio->nome,
            'laboratorio_email' => $inscrito->email,
            'empresa_nome_razao' => $inscrito->empresa->nome_razao,
            'empresa_cpf_cnpj' => $inscrito->empresa->cpf_cnpj,
            'interlab_nome' => $inscrito->agendaInterlab->interlab->nome,
            'analista_nome' => $analista->nome ?? null,
            'analista_email' => $analista->email ?? null,
        ];

        $dadosDoc = DadosGeraDoc::create([
            'content' => $content,
            'tipo' => 'tag_senha_analista',
        ]);

        $destinatarios = array_values(array_unique(array_filter(
            [$analista->email],
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
                'analista_id' => $analista->id,
                'analista_email' => $analista->email,
            ]);

            return $dadosDoc;
        }

        EnviaSenhaAnalistaJob::dispatch($dadosDoc->id, $destinatarios, $analista->id)
            ->delay(now()->addSeconds($delaySecs));

        return $dadosDoc;
    }
}

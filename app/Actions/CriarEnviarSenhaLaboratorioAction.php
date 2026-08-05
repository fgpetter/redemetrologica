<?php

namespace App\Actions;

use App\Exceptions\InvalidEmailException;
use App\Jobs\EnviaSenhaLaboratorioJob;
use App\Models\DadosGeraDoc;
use App\Models\InterlabInscrito;

class CriarEnviarSenhaLaboratorioAction
{
    /**
     * Cria registro de Carta Senha do laboratório e agenda o envio do e-mail com link.
     *
     * Content gravado em DadosGeraDoc (tipo `tag_senha`):
     */
    public function execute(InterlabInscrito $inscrito, int $delaySecs = 0): DadosGeraDoc
    {
        $inscrito->loadMissing(['laboratorio', 'empresa', 'agendaInterlab.interlab', 'pessoa']);

        if (empty($inscrito->tag_senha)) {
            $tagSenha = app(GerarTagSenhaInterlabAction::class)->execute(
                $inscrito->agendaInterlab,
                GerarTagSenhaInterlabAction::TIPO_LABORATORIO,
            );
            $inscrito->update(['tag_senha' => $tagSenha]);
            $inscrito->refresh();
        }

        /** @var array{
         *     participante_id: int,
         *     tag_senha: string|null,
         *     informacoes_inscricao: mixed,
         *     laboratorio_nome: string,
         *     laboratorio_email: string|null,
         *     empresa_nome_razao: string,
         *     empresa_cpf_cnpj: string,
         *     interlab_nome: string
         * } $content
         */
        $content = [
            'participante_id' => $inscrito->id,
            'tag_senha' => $inscrito->tag_senha,
            'informacoes_inscricao' => $inscrito->informacoes_inscricao,
            'laboratorio_nome' => $inscrito->laboratorio->nome,
            'laboratorio_email' => $inscrito->email,
            'empresa_nome_razao' => $inscrito->empresa->nome_razao,
            'empresa_cpf_cnpj' => $inscrito->empresa->cpf_cnpj,
            'interlab_nome' => $inscrito->agendaInterlab->interlab->nome,
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

            return $dadosDoc;
        }

        EnviaSenhaLaboratorioJob::dispatch($dadosDoc->id, $destinatarios, $inscrito->id)
            ->delay(now()->addSeconds($delaySecs));

        return $dadosDoc;
    }
}

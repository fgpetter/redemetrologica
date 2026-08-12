<?php

namespace App\Actions;

use App\Models\DadosGeraDoc;
use App\Models\InterlabAnalista;
use App\Models\InterlabInscrito;
use Illuminate\Support\Facades\DB;

class CriarCartaSenhaAnalistaAction
{
    /**
     * Garante a Tag Senha do analista e cria o registro de Carta Senha sem enviar e-mail.
     */
    public function execute(InterlabInscrito $inscrito, InterlabAnalista $analista): DadosGeraDoc
    {
        return DB::transaction(function () use ($inscrito, $analista) {
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

            return DadosGeraDoc::create([
                'content' => $content,
                'tipo' => 'tag_senha_analista',
            ]);
        });
    }
}

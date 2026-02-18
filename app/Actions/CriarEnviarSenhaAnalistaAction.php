<?php

namespace App\Actions;

use App\Models\DadosGeraDoc;
use App\Models\InterlabAnalista;
use App\Models\InterlabInscrito;
use App\Jobs\EnviarLinkSenhaInterlabJob;

class CriarEnviarSenhaAnalistaAction
{
    /**
     * Cria registro de tag senha e envia email com link para download
     *
     * @param InterlabInscrito $inscrito
     * @return DadosGeraDoc
     */
    public function execute(InterlabInscrito $inscrito, InterlabAnalista $analista = null, int $time): DadosGeraDoc
    {
        $inscrito->load(['laboratorio', 'empresa', 'agendaInterlab.interlab']);

        $dadosDoc = DadosGeraDoc::create([
            'content' => [
                'participante_id' => $inscrito->id,
                'tag_senha' => $inscrito->tag_senha,
                'informacoes_inscricao' => $inscrito->informacoes_inscricao,
                'laboratorio_nome' => $inscrito->laboratorio->nome,
                'laboratorio_email' => $inscrito->email,
                'empresa_nome_razao' => $inscrito->empresa->nome_razao,
                'empresa_cpf_cnpj' => $inscrito->empresa->cpf_cnpj,
                'interlab_nome' => $inscrito->agendaInterlab->interlab->nome,
                'analista_nome' => $analista->nome ?? null,
                'analista_email' => $analista->email ?? null,
            ],
            'tipo' => 'tag_senha',
        ]);

        EnviarLinkSenhaInterlabJob::dispatch($dadosDoc->id)->delay($time * 15);

        return $dadosDoc;
    }
}

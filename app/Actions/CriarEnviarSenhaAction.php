<?php

namespace App\Actions;

use App\Models\DadosGeraDoc;
use App\Models\InterlabInscrito;
use App\Jobs\EnviarLinkSenhaInterlabJob;

class CriarEnviarSenhaAction
{
    /**
     * Cria registro de tag senha e envia email com link para download
     *
     * @param InterlabInscrito $inscrito
     * @return DadosGeraDoc
     */
    public function execute(InterlabInscrito $inscrito, int $time): DadosGeraDoc
    {
        $inscrito->load(['laboratorio', 'empresa', 'agendaInterlab.interlab']);

        $dadosDoc = DadosGeraDoc::create([
            'content' => [
                'participante_id' => $inscrito->id,
                'tag_senha' => $inscrito->tag_senha,
                'informacoes_inscricao' => $inscrito->informacoes_inscricao,
                'laboratorio_nome' => $inscrito->laboratorio->nome,
                'laboratorio_email' => $inscrito->laboratorio->email,
                'empresa_nome_razao' => $inscrito->empresa->nome_razao,
                'empresa_cpf_cnpj' => $inscrito->empresa->cpf_cnpj,
                'interlab_nome' => $inscrito->agendaInterlab->interlab->nome,
            ],
            'tipo' => 'tag_senha',
        ]);

        EnviarLinkSenhaInterlabJob::dispatch($dadosDoc->id)->delay($time * 10);

        return $dadosDoc;
    }
}

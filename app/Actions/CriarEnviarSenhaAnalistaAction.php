<?php

namespace App\Actions;

use App\Actions\Interlab\CriarCartaSenhaAnalistaAction;
use App\Exceptions\InvalidEmailException;
use App\Jobs\EnviaSenhaAnalistaJob;
use App\Models\DadosGeraDoc;
use App\Models\InterlabAnalista;
use App\Models\InterlabInscrito;
use App\Support\ProcessosAgendados;

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
        bool $agendado = false,
    ): DadosGeraDoc {
        $dadosDoc = app(CriarCartaSenhaAnalistaAction::class)->execute($inscrito, $analista);

        $inscrito->loadMissing(['pessoa']);

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

        $envio = EnviaSenhaAnalistaJob::dispatch($dadosDoc->id, $destinatarios, $analista->id);

        if ($agendado) {
            $envio->onConnection('database')->onQueue(ProcessosAgendados::FILA_EMAIL);
        } else {
            $envio->delay(now()->addSeconds($delaySecs));
        }

        return $dadosDoc;
    }
}

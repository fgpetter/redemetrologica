<?php

namespace App\Integrations\Correios;

use Illuminate\Http\Client\Response;

class CorreiosRastroClient
{
    public const PATH_OBJETO = '/srorastro/v1/objetos';

    public function __construct(
        private CorreiosHttpClient $http,
        private CorreiosTokenClient $token,
    ) {}

    public function consultar(string $codigoObjeto): Response
    {
        $path = self::PATH_OBJETO.'/'.$codigoObjeto;

        CorreiosLog::info('rastro.client.consultar', [
            'path' => $path,
            'codigo_objeto' => $codigoObjeto,
            'resultado' => 'T',
        ]);

        $response = $this->http
            ->comBearer($this->token->bearer())
            ->get($path, [
                'resultado' => 'T',
            ]);

        CorreiosLog::response('rastro', $response);

        return $response;
    }
}

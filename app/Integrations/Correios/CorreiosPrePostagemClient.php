<?php

namespace App\Integrations\Correios;

use Illuminate\Http\Client\Response;
use InvalidArgumentException;

class CorreiosPrePostagemClient
{
    public const PATH_CRIAR = '/prepostagem/v1/prepostagens';

    public const PATH_CONSULTAR = '/prepostagem/v2/prepostagens';

    public const PATH_ROTULO = '/prepostagem/v1/prepostagens/rotulo/assincrono/pdf';

    public const PATH_DOWNLOAD = '/prepostagem/v1/prepostagens/rotulo/download/assincrono';

    public const PATH_DACE = '/prepostagem/v1/prepostagens/dce/dace/impressao';

    public const TAMANHO_GRUPO_ETIQUETA = 4;

    public function __construct(
        private CorreiosHttpClient $http,
        private CorreiosTokenClient $token,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function criar(array $payload): Response
    {
        CorreiosLog::info('prepostagem.client.criar', [
            'path' => self::PATH_CRIAR,
            'codigo_servico' => $payload['codigoServico'] ?? null,
        ]);

        $response = $this->http
            ->comBearer($this->token->bearer())
            ->post(self::PATH_CRIAR, $payload);

        CorreiosLog::response('prepostagem', $response);

        return $response;
    }

    public function consultar(string $idPrePostagem): Response
    {
        CorreiosLog::info('prepostagem.client.consultar', [
            'path' => self::PATH_CONSULTAR,
            'id' => $idPrePostagem,
        ]);

        $response = $this->http
            ->comBearer($this->token->bearer())
            ->get(self::PATH_CONSULTAR, [
                'id' => $idPrePostagem,
            ]);

        CorreiosLog::response('consulta_prepostagem', $response);

        return $response;
    }

    /**
     * @param  list<string>  $idsPrePostagem
     */
    public function solicitarRotulo(array $idsPrePostagem): Response
    {
        if (count($idsPrePostagem) > self::TAMANHO_GRUPO_ETIQUETA) {
            throw new InvalidArgumentException('O rótulo assíncrono aceita no máximo 4 idsPrePostagem.');
        }

        $payload = [
            'idsPrePostagem' => array_values($idsPrePostagem),
            'tipoRotulo' => (string) config('services.correios.tipo_rotulo', 'P'),
            'formatoRotulo' => (string) config('services.correios.formato_rotulo', 'ET'),
            'imprimeRemetente' => (string) config('services.correios.imprime_remetente', 'S'),
            'layoutImpressao' => (string) config('services.correios.layout_impressao', 'PADRAO'),
        ];

        CorreiosLog::info('prepostagem.client.solicitar_rotulo', [
            'path' => self::PATH_ROTULO,
            'quantidade' => count($idsPrePostagem),
            'payload' => $payload,
        ]);

        $response = $this->http
            ->comBearer($this->token->bearer())
            ->post(self::PATH_ROTULO, $payload);

        CorreiosLog::response('rotulo', $response);

        return $response;
    }

    public function downloadRotulo(string $idRecibo): Response
    {
        $path = self::PATH_DOWNLOAD.'/'.$idRecibo;

        CorreiosLog::info('prepostagem.client.download_rotulo', [
            'path' => $path,
            'id_recibo' => $idRecibo,
        ]);

        $response = $this->http
            ->comBearer($this->token->bearer())
            ->get($path);

        CorreiosLog::response('download', $response);

        return $response;
    }

    /**
     * @param  list<string>  $idsPrePostagens
     */
    public function imprimirDace(array $idsPrePostagens): Response
    {
        $payload = [
            'idsPrePostagens' => array_values($idsPrePostagens),
            'tipoDace' => (string) config('services.correios.tipo_dace', 'C'),
        ];

        CorreiosLog::info('prepostagem.client.imprimir_dace', [
            'path' => self::PATH_DACE,
            'quantidade' => count($idsPrePostagens),
            'payload' => $payload,
        ]);

        $response = $this->http
            ->comBearer($this->token->bearer())
            ->post(self::PATH_DACE, $payload);

        CorreiosLog::response('dace', $response);

        return $response;
    }
}

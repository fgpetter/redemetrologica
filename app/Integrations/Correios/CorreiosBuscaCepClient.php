<?php

namespace App\Integrations\Correios;

use App\Exceptions\CorreiosApiException;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\Client\ConnectionException;

class CorreiosBuscaCepClient
{
    private const PATH = '/cep/v2/enderecos';

    private const CODIGO_CEP_INEXISTENTE = 'CEP-003';

    public function __construct(
        private CorreiosHttpClient $http,
        private CorreiosTokenClient $token,
    ) {}

    /**
     * Retorna true para CEP confirmado, false para inexistente e null para consulta indisponível.
     *
     * @param  list<string>  $ceps
     * @return array{consultados: int, resultados: array<int|string, bool|null>}
     */
    public function consultar(array $ceps, string $tentativaId): array
    {
        $contexto = ['tentativa_id' => $tentativaId, 'path' => self::PATH.'/{cep}'];

        try {
            $bearer = $this->token->bearerContrato();
        } catch (CorreiosApiException $exception) {
            CorreiosLog::warning('cep.autenticacao_indisponivel', [
                ...$contexto,
                'path' => $exception->path,
                'status' => $exception->status,
                'codigo_erro' => $this->codigoErro($this->mensagemDaApi($exception->body)),
            ]);

            return ['consultados' => 0, 'resultados' => array_fill_keys($ceps, null)];
        } catch (ConnectionException|LockTimeoutException $exception) {
            CorreiosLog::warning('cep.autenticacao_indisponivel', [...$contexto, 'exception' => $exception::class]);

            return ['consultados' => 0, 'resultados' => array_fill_keys($ceps, null)];
        }

        $resultados = [];

        foreach ($ceps as $cep) {
            $resultados[$cep] = $this->consultarCep($cep, $bearer, $contexto);
        }

        return ['consultados' => count($resultados), 'resultados' => $resultados];
    }

    /** @param array{tentativa_id: string, path: string} $contexto */
    private function consultarCep(string $cep, string $bearer, array $contexto): ?bool
    {
        try {
            $response = $this->http
                ->comBearer($bearer, registrarRequisicao: false)
                ->get(self::PATH.'/'.$cep);
        } catch (ConnectionException $exception) {
            CorreiosLog::warning('cep.consulta_indisponivel', [
                ...$contexto,
                'cep' => $cep,
                'exception' => $exception::class,
            ]);

            return null;
        }

        $mensagem = $this->mensagemDaApi($response->json());
        $codigoErro = $this->codigoErro($mensagem);
        $contexto['status'] = $response->status();
        $contexto['codigo_erro'] = $codigoErro;

        if ($response->ok() && $response->json('cep') === $cep && $mensagem === null) {
            CorreiosLog::info('cep.confirmado', $contexto);

            return true;
        }

        $contexto['cep'] = $cep;

        if ($response->notFound() && $response->json('cep') === $cep && $codigoErro === self::CODIGO_CEP_INEXISTENTE) {
            CorreiosLog::warning('cep.recusado', $contexto);

            return false;
        }

        CorreiosLog::warning('cep.consulta_indisponivel', $contexto);

        return null;
    }

    private function mensagemDaApi(mixed $body): mixed
    {
        return data_get($body, 'txMsg') ?? data_get($body, 'msgs.0') ?? data_get($body, 'mensagem');
    }

    private function codigoErro(mixed $mensagem): ?string
    {
        if (is_string($mensagem) && preg_match('/^([A-Z]{2,12}-\d{1,6}):/', $mensagem, $partes) === 1) {
            return $partes[1];
        }

        return null;
    }
}

<?php

namespace App\Integrations\Correios;

use App\Exceptions\CorreiosApiException;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class CorreiosTokenClient
{
    private const CACHE_KEY = 'correios.token';

    private const CACHE_KEY_CONTRATO = 'correios.token.contrato';

    private const PATH = '/token/v1/autentica/cartaopostagem';

    private const PATH_CONTRATO = '/token/v1/autentica/contrato';

    public function __construct(private CorreiosHttpClient $http) {}

    public function bearer(): string
    {
        return $this->bearerPara(self::PATH, (string) config('services.correios.cartao_postagem'), self::CACHE_KEY);
    }

    public function bearerContrato(): string
    {
        return $this->bearerPara(self::PATH_CONTRATO, (string) config('services.correios.contrato'), self::CACHE_KEY_CONTRATO);
    }

    private function bearerPara(string $path, string $numero, string $cacheKey): string
    {
        $emCache = Cache::get($cacheKey);

        if (is_string($emCache) && $emCache !== '') {
            CorreiosLog::info('token.cache_hit', ['path' => $path]);

            return $emCache;
        }

        return Cache::lock($cacheKey.'.lock', 10)->block(5, function () use ($path, $numero, $cacheKey): string {
            $emCache = Cache::get($cacheKey);

            if (is_string($emCache) && $emCache !== '') {
                CorreiosLog::info('token.cache_hit', ['path' => $path, 'via' => 'lock']);

                return $emCache;
            }

            return $this->solicitar($path, $numero, $cacheKey);
        });
    }

    private function solicitar(string $path, string $numero, string $cacheKey): string
    {
        $porCartao = $path === self::PATH;
        CorreiosLog::info('token.solicitando', ['path' => $path]);

        $response = $this->http
            ->pending(registrarRequisicao: $porCartao)
            ->withBasicAuth(
                (string) config('services.correios.api_username'),
                (string) config('services.correios.api_key'),
            )
            ->asJson()
            ->post($path, [
                'numero' => $numero,
            ]);

        if ($porCartao) {
            CorreiosLog::response('token', $response);
        }

        if (! $response->successful()) {
            throw CorreiosApiException::fromResponse($response, $path);
        }

        $token = $response->json('token');

        if (! is_string($token) || $token === '') {
            throw new CorreiosApiException(
                'A API Token não retornou o Bearer.',
                $response->status(),
                null,
                $path,
            );
        }

        $expiraEm = $response->json('expiraEm');
        Cache::put($cacheKey, $token, $this->segundosDeCache($expiraEm, $response->status(), $path));

        CorreiosLog::info('token.cacheado', [
            'path' => $path,
            'expira_em' => $expiraEm,
        ]);

        return $token;
    }

    private function segundosDeCache(mixed $expiraEm, int $status, string $path): int
    {
        try {
            $expiracao = is_string($expiraEm) && $expiraEm !== '' ? Carbon::parse($expiraEm) : null;
        } catch (InvalidFormatException) {
            $expiracao = null;
        }

        if ($expiracao === null) {
            throw new CorreiosApiException('A API Token não retornou expiraEm válido.', $status, null, $path);
        }

        $limite = $expiracao->subMinutes(5)->getTimestamp() - now()->getTimestamp();

        return max(1, $limite);
    }
}

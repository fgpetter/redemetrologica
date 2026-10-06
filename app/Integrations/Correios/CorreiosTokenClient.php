<?php

namespace App\Integrations\Correios;

use App\Exceptions\CorreiosApiException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class CorreiosTokenClient
{
    private const CACHE_KEY = 'correios.token';

    private const LOCK_KEY = 'correios.token.lock';

    private const PATH = '/token/v1/autentica/cartaopostagem';

    public function __construct(private CorreiosHttpClient $http) {}

    public function bearer(): string
    {
        $emCache = Cache::get(self::CACHE_KEY);

        if (is_string($emCache) && $emCache !== '') {
            CorreiosLog::info('token.cache_hit', ['path' => self::PATH]);

            return $emCache;
        }

        return Cache::lock(self::LOCK_KEY, 10)->block(5, function (): string {
            $emCache = Cache::get(self::CACHE_KEY);

            if (is_string($emCache) && $emCache !== '') {
                CorreiosLog::info('token.cache_hit', ['path' => self::PATH, 'via' => 'lock']);

                return $emCache;
            }

            return $this->solicitar();
        });
    }

    private function solicitar(): string
    {
        CorreiosLog::info('token.solicitando', [
            'path' => self::PATH,
            'usuario' => (string) config('services.correios.api_username'),
            'cartao_postagem' => (string) config('services.correios.cartao_postagem'),
        ]);

        $response = $this->http
            ->pending()
            ->withBasicAuth(
                (string) config('services.correios.api_username'),
                (string) config('services.correios.api_key'),
            )
            ->asJson()
            ->post(self::PATH, [
                'numero' => (string) config('services.correios.cartao_postagem'),
            ]);

        CorreiosLog::response('token', $response);

        if (! $response->successful()) {
            throw CorreiosApiException::fromResponse($response, self::PATH);
        }

        $token = (string) $response->json('token');

        if ($token === '') {
            throw new CorreiosApiException(
                'A API Token não retornou o Bearer.',
                $response->status(),
                is_array($response->json()) ? $response->json() : null,
                self::PATH,
            );
        }

        $expiraEm = $response->json('expiraEm');
        Cache::put(self::CACHE_KEY, $token, $this->segundosDeCache($expiraEm));

        CorreiosLog::info('token.cacheado', [
            'expira_em' => $expiraEm,
        ]);

        return $token;
    }

    private function segundosDeCache(mixed $expiraEm): int
    {
        if (! is_string($expiraEm) || $expiraEm === '') {
            throw new CorreiosApiException(
                'A API Token não retornou expiraEm.',
                201,
                null,
                self::PATH,
            );
        }

        $limite = Carbon::parse($expiraEm)->subMinutes(5)->getTimestamp() - now()->getTimestamp();

        return max(1, $limite);
    }
}

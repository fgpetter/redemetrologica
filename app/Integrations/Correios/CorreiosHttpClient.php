<?php

namespace App\Integrations\Correios;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class CorreiosHttpClient
{
    public function pending(bool $registrarRequisicao = true): PendingRequest
    {
        $request = Http::baseUrl(rtrim((string) config('services.correios.base_url'), '/'))
            ->connectTimeout(3)
            ->timeout((int) config('services.correios.timeout', 15))
            ->acceptJson();

        if ($registrarRequisicao) {
            $request->beforeSending(function ($request): void {
                CorreiosLog::request($request);
            });
        }

        return $request;
    }

    public function comBearer(string $token, bool $registrarRequisicao = true): PendingRequest
    {
        return $this->pending($registrarRequisicao)
            ->withToken($token)
            ->asJson();
    }
}

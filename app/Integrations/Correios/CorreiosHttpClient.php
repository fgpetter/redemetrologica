<?php

namespace App\Integrations\Correios;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class CorreiosHttpClient
{
    public function pending(): PendingRequest
    {
        return Http::baseUrl(rtrim((string) config('services.correios.base_url'), '/'))
            ->connectTimeout(3)
            ->timeout((int) config('services.correios.timeout', 15))
            ->acceptJson()
            ->beforeSending(function ($request): void {
                CorreiosLog::request($request);
            });
    }

    public function comBearer(string $token): PendingRequest
    {
        return $this->pending()
            ->withToken($token)
            ->asJson();
    }
}

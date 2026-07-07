<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ValidatePepApiKey
{
    /**
     * Valida a chave de API enviada no corpo da requisição para a API PEP.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('Accept', 'application/json');

        $apiKey = (string) $request->input('api_key');

        if ($apiKey === '') {
            return response()->json(['message' => 'Chave de API não informada.'], 401);
        }

        if (! hash_equals((string) config('services.pep.api_key'), $apiKey)) {
            Log::warning('Tentativa de acesso à API PEP com chave inválida.', ['ip' => $request->ip()]);

            return response()->json(['message' => 'Chave de API inválida.'], 401);
        }

        return $next($request);
    }
}

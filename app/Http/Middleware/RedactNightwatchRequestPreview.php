<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Nightwatch\Core;
use Laravel\Nightwatch\State\RequestState;

class RedactNightwatchRequestPreview
{
    public function handle(Request $request, Closure $next): mixed
    {
        $state = app(Core::class)->executionState;

        // Nightwatch 1.30 também usa o caminho bruto em previews de eventos filhos.
        // O callback redactRequests, isoladamente, não protege esses previews.
        if ($state instanceof RequestState) {
            $state->executionPreview = $request->method().' [rota omitida]';
        }

        return $next($request);
    }
}

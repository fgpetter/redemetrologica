<?php

namespace App\Providers;

use App\Logging\ContextualIngest;
use App\Support\NightwatchRedaction;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Laravel\Nightwatch\Core;
use Laravel\Nightwatch\Facades\Nightwatch;
use Laravel\Nightwatch\Records\CacheEvent;
use Laravel\Nightwatch\Records\Command;
use Laravel\Nightwatch\Records\Exception;
use Laravel\Nightwatch\Records\Mail;
use Laravel\Nightwatch\Records\OutgoingRequest;
use Laravel\Nightwatch\Records\Request;
use Throwable;

class NightwatchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Nightwatch::handleUnrecoverableExceptionsUsing(function (Throwable $exception): void {
            Log::channel('single')->error('Falha interna do Nightwatch.', ['exception' => $exception]);
        });
    }

    public function boot(): void
    {
        $core = app(Core::class);

        if (! $core->ingest instanceof ContextualIngest) {
            $core->ingest = new ContextualIngest($core->ingest);
        }

        Nightwatch::user(fn (Authenticatable $user): array => ['id' => $user->uid ?? '', 'name' => '', 'username' => '']);

        Nightwatch::redactRequests(function (Request $request): void {
            // Usar o template da rota evita expor tokens e dados em segmentos da URL.
            $request->url = rtrim(config('app.url'), '/').'/'.ltrim($request->routePath, '/');
            $request->ip = '';
            $request->headers->replace([]);
            $request->payload->replace([]);
            $request->files->replace([]);
        });

        Nightwatch::redactOutgoingRequests(function (OutgoingRequest $request): void {
            $request->url = NightwatchRedaction::url($request->url);
        });

        Nightwatch::redactExceptions(function (Exception $exception): void {
            $exception->message = NightwatchRedaction::text($exception->message);
            app(Core::class)->executionState->exceptionPreview = $exception->message;
        });

        Nightwatch::redactCommands(function (Command $command): void {
            $command->command = $command->name;
        });

        Nightwatch::redactMail(function (Mail $mail): void {
            $mail->subject = '[omitido]';
        });

        Nightwatch::redactCacheEvents(function (CacheEvent $event): void {
            $event->key = '[omitido]';
        });
    }
}

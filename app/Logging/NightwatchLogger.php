<?php

namespace App\Logging;

use App\Support\NightwatchRedaction;
use Laravel\Nightwatch\Factories\Logger;
use Monolog\Handler\HandlerWrapper;
use Monolog\Logger as Monolog;
use Monolog\LogRecord;

class NightwatchLogger
{
    public function __construct(private Logger $factory) {}

    public function __invoke(array $config): Monolog
    {
        /** @var Monolog $logger */
        $logger = ($this->factory)($config);

        // Sanitizar somente o destino remoto, sem alterar registros locais do stack.
        $logger->setHandlers(array_map(fn ($handler) => new class($handler) extends HandlerWrapper
        {
            public function handle(LogRecord $record): bool
            {
                return $this->handler->handle($record->with(
                    message: NightwatchRedaction::text($record->message),
                    context: NightwatchRedaction::context($record->context),
                    extra: NightwatchRedaction::context($record->extra),
                ));
            }
        }, $logger->getHandlers()));

        return $logger;
    }
}

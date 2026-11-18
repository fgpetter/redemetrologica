<?php

namespace App\Logging;

use App\Support\NightwatchRedaction;
use App\Support\Observabilidade\ContextoOperacional;
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
        $logger->setHandlers(array_map(fn ($handler) => new class($handler, $config['operacional'] ?? false) extends HandlerWrapper
        {
            public function __construct($handler, private bool $operacional)
            {
                parent::__construct($handler);
            }

            public function handle(LogRecord $record): bool
            {
                if ($this->operacional) {
                    $contexto = ContextoOperacional::selecionar($record->context);
                    if (! isset($contexto['evento'])) {
                        return false;
                    }
                    $record = $record->with(message: $contexto['evento'], context: $contexto, extra: []);
                }

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

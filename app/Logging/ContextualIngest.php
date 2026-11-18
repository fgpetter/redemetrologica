<?php

namespace App\Logging;

use App\Support\Observabilidade\ContextoOperacional;
use Illuminate\Support\Facades\Context;
use Laravel\Nightwatch\Contracts\Ingest;

/**
 * Acrescenta ao evento de exceção a correlação operacional do escopo atual.
 *
 * O sensor de exceções do Nightwatch 1.30 não serializa o Context público;
 * os demais eventos (request, command, job, log) já o possuem.
 */
final class ContextualIngest implements Ingest
{
    public function __construct(private Ingest $ingest) {}

    public function write(array $record): void
    {
        $this->ingest->write($this->correlacionar($record));
    }

    public function writeNow(array $record): void
    {
        $this->ingest->writeNow($this->correlacionar($record));
    }

    public function ping(): void
    {
        $this->ingest->ping();
    }

    public function shouldDigest(bool $bool = true): void
    {
        $this->ingest->shouldDigest($bool);
    }

    public function shouldDigestWhenBufferIsFull(bool $bool = true): void
    {
        $this->ingest->shouldDigestWhenBufferIsFull($bool);
    }

    public function digest(): void
    {
        $this->ingest->digest();
    }

    public function flush(): void
    {
        $this->ingest->flush();
    }

    /** @param array<mixed> $record */
    private function correlacionar(array $record): array
    {
        if (($record['t'] ?? null) !== 'exception') {
            return $record;
        }

        $contexto = ContextoOperacional::selecionar(Context::all());

        if ($contexto === []) {
            return $record;
        }

        $record['context'] = json_encode((object) $contexto, flags: JSON_INVALID_UTF8_SUBSTITUTE | JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return $record;
    }
}

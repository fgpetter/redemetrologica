<?php

namespace App\Actions;

use App\Console\Commands\EnviarEmailsAutomaticosCursos;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CoordenarCacheEmailsCursos
{
    private const LOCK = 'cursos:emails-automaticos:coordenacao';

    private const VERSAO = 'cursos:emails-automaticos:versao';

    public function versao(): ?string
    {
        return Cache::get(self::VERSAO);
    }

    public function registrarEsgotamento(?string $versao, CarbonImmutable $expiraEm): bool
    {
        return Cache::lock(self::LOCK, 10)->block(5, function () use ($versao, $expiraEm): bool {
            return $this->versao() === $versao
                && Cache::add(EnviarEmailsAutomaticosCursos::SEM_EMAIL_PENDENTE_CACHE_KEY, true, $expiraEm);
        });
    }

    public function invalidar(): void
    {
        Cache::lock(self::LOCK, 10)->block(5, function (): void {
            Cache::forever(self::VERSAO, (string) Str::uuid());
            Cache::forget(EnviarEmailsAutomaticosCursos::SEM_EMAIL_PENDENTE_CACHE_KEY);
        });
    }
}

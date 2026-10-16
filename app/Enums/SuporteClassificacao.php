<?php

namespace App\Enums;

enum SuporteClassificacao: string
{
    case Corretiva = 'corretiva';
    case Evolutiva = 'evolutiva';

    public function label(): string
    {
        return match ($this) {
            self::Corretiva => 'Corretiva',
            self::Evolutiva => 'Evolutiva',
        };
    }
}

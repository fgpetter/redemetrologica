<?php

namespace App\Enums;

enum EsferaGovernamental: string
{
    case Federal = 'federal';
    case Estadual = 'estadual';
    case Municipal = 'municipal';

    public function label(): string
    {
        return match ($this) {
            self::Federal => 'Federal',
            self::Estadual => 'Estadual',
            self::Municipal => 'Municipal',
        };
    }
}

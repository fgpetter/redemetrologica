<?php

namespace App\Enums;

enum ImpostoAvaliacao: string
{
    case Padrao = 'padrao';

    public function fator(): float
    {
        return match ($this) {
            self::Padrao => 0.053,
        };
    }
}

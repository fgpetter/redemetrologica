<?php

namespace App\Enums;

enum FornecedorArea: string
{
    case Interlaboratorial = 'interlaboratorial';
    case Curso             = 'curso';
    case Avaliacao         = 'avaliacao';

    public function label(): string
    {
        return match ($this) {
            self::Interlaboratorial => 'Interlaboratorial',
            self::Curso             => 'Curso',
            self::Avaliacao         => 'Avaliação',
        };
    }
}

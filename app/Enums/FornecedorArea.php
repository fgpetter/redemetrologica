<?php

namespace App\Enums;

enum FornecedorArea: string
{
    case Geral     = 'geral';
    case PEP       = 'PEP';
    case Curso     = 'curso';
    case Avaliacao = 'avaliacao';

    public function label(): string
    {
        return match ($this) {
            self::Geral     => 'Geral',
            self::PEP       => 'PEP',
            self::Curso     => 'Curso',
            self::Avaliacao => 'Avaliação',
        };
    }
}

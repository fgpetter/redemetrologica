<?php

namespace App\Enums;

enum InterlabLotePostagemEtiquetaStatus: string
{
    case Pendente = 'pendente';
    case Solicitada = 'solicitada';
    case Gerada = 'gerada';
    case Erro = 'erro';

    public function label(): string
    {
        return match ($this) {
            self::Pendente => 'Pendente',
            self::Solicitada => 'Solicitada',
            self::Gerada => 'Gerada',
            self::Erro => 'Erro',
        };
    }
}

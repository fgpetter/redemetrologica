<?php

namespace App\Enums;

enum CorreiosFormatoObjeto: string
{
    case Envelope = '1';
    case CaixaPacote = '2';
    case CilindroRolo = '3';

    public function label(): string
    {
        return match ($this) {
            self::Envelope => 'Envelope',
            self::CaixaPacote => 'Caixa/pacote',
            self::CilindroRolo => 'Cilindro/rolo',
        };
    }
}

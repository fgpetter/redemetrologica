<?php

namespace App\Enums;

enum FormaPagamentoCobranca: string
{
    case BoletoBancario = 'boleto_bancario';
    case DepositoBancario = 'deposito_bancario';

    public function label(): string
    {
        return match ($this) {
            self::BoletoBancario => 'Boleto bancário',
            self::DepositoBancario => 'Depósito bancário',
        };
    }
}

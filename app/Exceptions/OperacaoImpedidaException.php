<?php

namespace App\Exceptions;

use RuntimeException;

final class OperacaoImpedidaException extends RuntimeException
{
    public function __construct(public readonly string $motivo, string $mensagem)
    {
        parent::__construct($mensagem);
    }
}

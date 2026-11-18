<?php

namespace App\Exceptions;

use RuntimeException;

/** Interrompe a transação de simulação; não representa incidente técnico. */
final class SimulacaoConcluidaException extends RuntimeException {}

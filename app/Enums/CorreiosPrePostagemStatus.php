<?php

namespace App\Enums;

enum CorreiosPrePostagemStatus: int
{
    case Preatendido = 1;
    case Prepostado = 2;
    case Postado = 3;
    case Expirado = 4;
    case Cancelado = 5;
    case Estornado = 6;
    case Pendente = 7;
}

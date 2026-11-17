<?php

namespace App\Helpers;

use NumberFormatter;

class ValorPorExtenso
{
    public static function reais(int $centavos): string
    {
        $formatter = new NumberFormatter('pt_BR', NumberFormatter::SPELLOUT);
        $reais = intdiv($centavos, 100);
        $resto = $centavos % 100;
        $partes = [];

        if ($reais > 0 || $resto === 0) {
            $unidade = $reais === 1 ? 'real' : 'reais';
            $conector = $reais >= 1000000 && $reais % 1000000 === 0 ? ' de ' : ' ';
            $partes[] = $formatter->format($reais).$conector.$unidade;
        }

        if ($resto > 0) {
            $partes[] = $formatter->format($resto).($resto === 1 ? ' centavo' : ' centavos');
        }

        return implode(' e ', $partes);
    }
}

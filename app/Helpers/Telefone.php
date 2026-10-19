<?php

namespace App\Helpers;

class Telefone
{
    /**
     * @param  array<string, mixed>  $campos
     * @return array<string, mixed>
     */
    public static function prepararCampos(array $campos): array
    {
        foreach ($campos as $campo => $valor) {
            $campos[$campo] = self::paraValidacao($valor);
        }

        return $campos;
    }

    public static function paraValidacao(mixed $valor): mixed
    {
        if (! is_string($valor)) {
            return $valor;
        }

        if (preg_match('/\A[0-9]{10,11}\z/', $valor) === 1) {
            return self::formatado($valor);
        }

        if (preg_match('/\A\(([0-9]{2})\) ([0-9]{4})-([0-9]{4})\z/', $valor, $matches) === 1) {
            return "({$matches[1]}) {$matches[2]}-{$matches[3]}";
        }

        if (preg_match('/\A\(([0-9]{2})\) ([0-9]{5})-([0-9]{4})\z/', $valor, $matches) === 1) {
            return "({$matches[1]}) {$matches[2]}-{$matches[3]}";
        }

        if (preg_match('/\A\(([0-9]{2})\) 9 ([0-9]{4})-([0-9]{4})\z/', $valor, $matches) === 1) {
            return "({$matches[1]}) 9{$matches[2]}-{$matches[3]}";
        }

        return $valor;
    }

    public static function somenteNumeros(?string $valor): ?string
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        return preg_replace('/\D/', '', $valor);
    }

    private static function formatado(string $digitos): string
    {
        if (strlen($digitos) === 11) {
            return sprintf('(%s) %s-%s', substr($digitos, 0, 2), substr($digitos, 2, 5), substr($digitos, 7));
        }

        return sprintf('(%s) %s-%s', substr($digitos, 0, 2), substr($digitos, 2, 4), substr($digitos, 6));
    }
}

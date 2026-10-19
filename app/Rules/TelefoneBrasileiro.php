<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TelefoneBrasileiro implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || preg_match('/\A[0-9() -]+\z/', $value) !== 1) {
            $fail('Informe um telefone válido com DDD (fixo ou celular).');

            return;
        }

        $digitos = preg_replace('/\D/', '', $value);
        $ddd = '(?:1[1-9]|2[12478]|3[1-578]|4[1-9]|5[13-5]|6[1-9]|7[134579]|8[1-9]|9[1-9])';

        if (preg_match('/\A'.$ddd.'(?:[2-5][0-9]{7}|9[0-9]{8})\z/', $digitos) !== 1) {
            $fail('Informe um telefone válido com DDD (fixo ou celular).');
        }
    }
}

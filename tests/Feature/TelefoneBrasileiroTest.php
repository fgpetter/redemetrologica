<?php

use App\Rules\TelefoneBrasileiro;
use Illuminate\Support\Facades\Validator;

it('aceita telefones nacionais com DDD com ou sem máscara', function (string $telefone): void {
    $validator = Validator::make(['telefone' => $telefone], [
        'telefone' => ['required', new TelefoneBrasileiro],
    ]);

    expect($validator->errors()->all())->toBe([]);
})->with([
    'fixo sem máscara' => '1133334444',
    'fixo com máscara' => '(11) 3333-4444',
    'celular sem máscara' => '11988887777',
    'celular com máscara' => '(11) 98888-7777',
    'celular com a máscara da tela' => '(11) 9 8888-7777',
    'fixo com prefixo 5' => '(51) 5333-4444',
    'DDD 55 não é código do país' => '(55) 99999-8888',
    'DDD 99' => '(99) 99999-8888',
]);

it('rejeita telefones com DDD prefixo tamanho ou caracteres inválidos', function (mixed $telefone): void {
    $validator = Validator::make(['telefone' => $telefone], [
        'telefone' => ['required', new TelefoneBrasileiro],
    ]);

    expect($validator->errors()->get('telefone'))
        ->toBe(['Informe um telefone válido com DDD (fixo ou celular).']);
})->with([
    'sem DDD' => '988887777',
    'DDD zero' => '(00) 98888-7777',
    'DDD inexistente 20' => '(20) 98888-7777',
    'DDD inexistente 36' => '(36) 98888-7777',
    'DDD inexistente 52' => '(52) 98888-7777',
    'DDD inexistente 72' => '(72) 98888-7777',
    'prefixo de fixo inválido' => '(11) 1234-5678',
    'celular sem nono dígito' => '(11) 8888-7777',
    'celular iniciando com 8' => '(11) 88888-7777',
    'dígitos excedentes' => '119888877777',
    'letras misturadas ao número' => '(11) 98888-7777abc',
    'letras em lugar de telefone' => 'telefone',
    'código de país fora do formato nacional da tela' => '+55 (11) 98888-7777',
    'tipo inválido' => 11988887777,
]);

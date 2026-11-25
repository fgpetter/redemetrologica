<?php

use App\Integrations\Correios\CorreiosBuscaCepClient;
use App\Integrations\Correios\CorreiosTokenClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

test('usa o bearer do contrato mesmo se o token do cartão já estiver no cache', function () {
    configurarPrePostagemCorreios();

    Http::fake([
        '*/token/v1/autentica/cartaopostagem' => respostaTokenCorreios(),
        '*/token/v1/autentica/contrato' => Http::response([
            'token' => 'bearer-contrato-teste',
            'expiraEm' => now()->addDay()->toIso8601String(),
        ], 201),
        '*/cep/v2/enderecos/01001001' => Http::response(['cep' => '01001001']),
        '*/cep/v2/enderecos/01001011' => Http::response([
            'cep' => '01001011',
            'txMsg' => 'CEP-003: O CEP 01001011 não foi encontrado',
        ], 404),
    ]);

    $token = app(CorreiosTokenClient::class);
    $cliente = app(CorreiosBuscaCepClient::class);

    expect($token->bearer())->toBe('bearer-teste')
        ->and($cliente->consultar(['01001001', '01001011'], 'tentativa-teste'))->toBe([
            'consultados' => 2,
            'resultados' => ['01001001' => true, '01001011' => false],
        ])
        ->and($token->bearer())->toBe('bearer-teste')
        ->and($token->bearerContrato())->toBe('bearer-contrato-teste');

    Http::assertSentCount(4);
    Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/token/v1/autentica/contrato')
        && $request->method() === 'POST'
        && $request->data() === ['numero' => config('services.correios.contrato')]);
    Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/cep/v2/enderecos/01001001')
        && $request->method() === 'GET'
        && $request->hasHeader('Authorization', 'Bearer bearer-contrato-teste'));
});

test('mantém a consulta indisponível quando a resposta não confirma o CEP', function (int $http, array|string $body) {
    configurarPrePostagemCorreios();

    Http::fake([
        '*/token/v1/autentica/contrato' => respostaTokenCorreios(),
        '*/cep/v2/enderecos/01001001' => Http::response($body, $http),
    ]);

    expect(app(CorreiosBuscaCepClient::class)->consultar(['01001001'], 'tentativa-teste'))->toBe([
        'consultados' => 1,
        'resultados' => ['01001001' => null],
    ]);
})->with([
    '404 genérico' => [404, ['message' => 'Not Found']],
    '404 com outro CEP' => [404, ['cep' => '01001011', 'txMsg' => 'CEP-003: O CEP 01001011 não foi encontrado']],
    '404 com CEP mas sem o código previsto' => [404, ['cep' => '01001001']],
    'resposta de autenticação' => [401, ['txMsg' => 'Acesso negado']],
    'limite de requisições' => [429, []],
    'indisponibilidade do servidor' => [503, []],
    'corpo malformado' => [200, '<html>Erro</html>'],
    '200 com outro CEP' => [200, ['cep' => '01001011']],
]);

test('continua consultando quando um CEP não responde a tempo', function () {
    configurarPrePostagemCorreios();

    Http::fake([
        '*/token/v1/autentica/contrato' => respostaTokenCorreios(),
        '*/cep/v2/enderecos/01001001' => Http::failedConnection('Timeout'),
        '*/cep/v2/enderecos/01001011' => Http::response(['cep' => '01001011']),
    ]);

    expect(app(CorreiosBuscaCepClient::class)->consultar(['01001001', '01001011'], 'tentativa-teste'))->toBe([
        'consultados' => 2,
        'resultados' => ['01001001' => null, '01001011' => true],
    ]);
});

test('pede o token uma única vez na tentativa quando o contrato é recusado', function () {
    configurarPrePostagemCorreios();

    Http::fake([
        '*/token/v1/autentica/contrato' => Http::response(['msgs' => ['Acesso negado']], 403),
    ]);

    expect(app(CorreiosBuscaCepClient::class)->consultar(['01001001', '01001011'], 'tentativa-teste'))->toBe([
        'consultados' => 0,
        'resultados' => ['01001001' => null, '01001011' => null],
    ]);

    Http::assertSentCount(1);
});

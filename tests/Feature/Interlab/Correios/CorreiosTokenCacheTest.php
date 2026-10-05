<?php

use App\Integrations\Correios\CorreiosPrazoClient;
use App\Models\InterlabLotePostagemItem;
use Database\Factories\InterlabLotePostagemFactory;
use Database\Factories\InterlabLotePostagemItemFactory;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

test('segunda chamada de negocio nao solicita outro token', function () {
    configurarCredenciaisCorreios();

    Http::preventStrayRequests();
    Http::fake(function (Request $request) {
        if (str_contains($request->url(), '/token/v1/autentica/cartaopostagem')) {
            return Http::response([
                'token' => 'bearer-teste',
                'expiraEm' => now()->addDay()->toIso8601String(),
            ], 201);
        }

        return Http::response([], 200);
    });

    $lote = InterlabLotePostagemFactory::new()->create();
    InterlabLotePostagemItemFactory::new()->create([
        'interlab_lote_postagem_id' => $lote->id,
    ]);
    $itens = InterlabLotePostagemItem::query()
        ->where('interlab_lote_postagem_id', $lote->id)
        ->get();

    $client = app(CorreiosPrazoClient::class);
    $client->consultarNacional($lote->uid, $itens);
    $client->consultarNacional($lote->uid, $itens);

    $tokens = Http::recorded()->filter(
        fn (array $par): bool => str_contains($par[0]->url(), '/token/v1/autentica/cartaopostagem'),
    );

    expect($tokens)->toHaveCount(1)
        ->and($tokens->first()[0]->data()['numero'])->toBe('0066547563');
});

function configurarCredenciaisCorreios(): void
{
    config([
        'services.correios.base_url' => 'https://api.correios.com.br',
        'services.correios.contrato' => '9912322361',
        'services.correios.cartao_postagem' => '0066547563',
        'services.correios.api_username' => 'rede.200',
        'services.correios.api_key' => 'chave-teste',
        'services.correios.codigo_servico_sedex_12' => '03140',
        'services.correios.codigo_servico_sedex' => '03220',
        'services.correios.timeout' => 15,
        'services.correios.admin_email' => 'sistema@redemetrologica.com.br',
        'services.correios.remetente.cep' => '90010-000',
    ]);
}

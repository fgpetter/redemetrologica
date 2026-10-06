<?php

use App\Enums\InterlabLotePostagemStatus;
use App\Models\InterlabLotePostagem;
use Database\Factories\InterlabLotePostagemFactory;
use Database\Factories\InterlabLotePostagemItemFactory;
use Illuminate\Support\Facades\Http;

function configurarPrePostagemCorreios(): void
{
    config([
        'services.correios.base_url' => 'https://api.correios.com.br',
        'services.correios.contrato' => '9912322361',
        'services.correios.cartao_postagem' => '0066547563',
        'services.correios.api_username' => 'rede.200',
        'services.correios.api_key' => 'chave-teste',
        'services.correios.codigo_servico_sedex_12' => '03140',
        'services.correios.codigo_servico_sedex' => '03220',
        'services.correios.tipo_rotulo' => 'P',
        'services.correios.formato_rotulo' => 'ET',
        'services.correios.imprime_remetente' => 'S',
        'services.correios.layout_impressao' => 'PADRAO',
        'services.correios.timeout' => 15,
        'services.correios.admin_email' => 'sistema@redemetrologica.com.br',
        'services.correios.documentos_email' => [
            'tecnico@redemetrologica.com.br',
            'interlab@redemetrologica.com.br',
        ],
        'services.correios.remetente' => [
            'nome' => 'Rede Metrologica',
            'cpf_cnpj' => '97130207000112',
            'cep' => '91030330',
            'logradouro' => 'Santa Catarina',
            'numero' => '40',
            'complemento' => 'Salas 801/802',
            'bairro' => 'Santa Maria Goretti',
            'cidade' => 'Porto Alegre',
            'uf' => 'RS',
        ],
    ]);

    Http::preventStrayRequests();
}

function configurarClassificacaoCorreios(): void
{
    configurarPrePostagemCorreios();

    config([
        'services.correios.remetente.cep' => '90010-000',
    ]);
}

function loteComItensClassificados(int $quantidade = 1): InterlabLotePostagem
{
    $lote = InterlabLotePostagemFactory::new()->create([
        'status' => InterlabLotePostagemStatus::GerandoPrePostagens,
        'ultima_progressao_em' => now(),
    ]);

    InterlabLotePostagemItemFactory::new()
        ->count($quantidade)
        ->classificadoSedex12()
        ->create(['interlab_lote_postagem_id' => $lote->id]);

    return $lote->load('itens');
}

function respostaTokenCorreios(): \GuzzleHttp\Promise\PromiseInterface
{
    return Http::response([
        'token' => 'bearer-teste',
        'expiraEm' => now()->addDay()->toIso8601String(),
    ], 201);
}

/**
 * @param  callable(array<string, string>): array<string, mixed>  $resultado
 */
function respostaPrazoCorreios(\Illuminate\Http\Client\Request $request, callable $resultado): \GuzzleHttp\Promise\PromiseInterface
{
    if (str_contains($request->url(), '/token/')) {
        return respostaTokenCorreios();
    }

    $resultados = collect($request->data()['parametrosPrazo'] ?? [])
        ->map(fn (array $parametro): array => $resultado($parametro))
        ->all();

    return Http::response($resultados, 200);
}

/**
 * @return \Illuminate\Support\Collection<int, \Illuminate\Http\Client\Request>
 */
function consultasPrazoEnviadas(): \Illuminate\Support\Collection
{
    return Http::recorded()
        ->filter(fn (array $par): bool => str_contains($par[0]->url(), '/prazo/v1/nacional'))
        ->map(fn (array $par): \Illuminate\Http\Client\Request => $par[0])
        ->values();
}

/**
 * @param  list<array<string, mixed>>  $eventos
 * @return array<string, mixed>
 */
function respostaRastroCorreios(string $codigoObjeto, array $eventos = [], ?string $mensagem = null): array
{
    $objeto = [
        'codObjeto' => $codigoObjeto,
        'tipoPostal' => [
            'sigla' => 'DG',
            'descricao' => 'SEDEX',
            'categoria' => 'SEDEX',
        ],
        'eventos' => $eventos,
    ];

    if ($mensagem !== null) {
        $objeto['mensagem'] = $mensagem;
    }

    return [
        'versao' => '3.5.49',
        'quantidade' => 1,
        'objetos' => [$objeto],
        'tipoResultado' => 'Todos os Eventos',
    ];
}

/**
 * @return list<array<string, mixed>>
 */
function eventosRastroEntrega(): array
{
    return [
        [
            'codigo' => 'PO',
            'tipo' => '01',
            'dtHrCriado' => '2026-09-10T10:00:00',
            'descricao' => 'Objeto postado',
        ],
        [
            'codigo' => 'BDE',
            'tipo' => '01',
            'dtHrCriado' => '2026-09-12T17:03:00',
            'descricao' => 'Objeto entregue ao destinatário',
        ],
    ];
}

/**
 * @return list<array<string, mixed>>
 */
function eventosRastroEmTransito(): array
{
    return [
        [
            'codigo' => 'PO',
            'tipo' => '01',
            'dtHrCriado' => '2026-09-10T10:00:00',
            'descricao' => 'Objeto postado',
        ],
        [
            'codigo' => 'DO',
            'tipo' => '01',
            'dtHrCriado' => '2026-09-11T08:00:00',
            'descricao' => 'Objeto em trânsito',
        ],
    ];
}

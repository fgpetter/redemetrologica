<?php

namespace App\Integrations\Correios;

use App\Models\InterlabLotePostagemItem;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class CorreiosPrazoClient
{
    public const TAMANHO_LOTE = 5;

    public const PATH = '/prazo/v1/nacional';

    public function __construct(
        private CorreiosHttpClient $http,
        private CorreiosTokenClient $token,
    ) {}

    /**
     * Consulta a elegibilidade do SEDEX 12. Não lança em 4xx/5xx: a Action decide
     * entre classificar, retentar ou encerrar o lote.
     *
     * @param  Collection<int, InterlabLotePostagemItem>  $itens
     */
    public function consultarNacional(string $idLote, Collection $itens): Response
    {
        $payload = $this->payload($idLote, $itens);

        CorreiosLog::info('prazo.consultando', [
            'path' => self::PATH,
            'id_lote' => $idLote,
            'quantidade' => $itens->count(),
            'payload' => $payload,
        ]);

        $response = $this->http
            ->comBearer($this->token->bearer())
            ->post(self::PATH, $payload);

        CorreiosLog::response('prazo', $response);

        return $response;
    }

    /**
     * @param  Collection<int, InterlabLotePostagemItem>  $itens
     * @return array{idLote: string, parametrosPrazo: list<array{coProduto: string, nuRequisicao: string, cepOrigem: string, cepDestino: string, dataPostagem: string}>}
     */
    public function payload(string $idLote, Collection $itens): array
    {
        if ($itens->count() > self::TAMANHO_LOTE) {
            throw new InvalidArgumentException('A API Prazo aceita no máximo 5 consultas por requisição.');
        }

        $cepOrigem = preg_replace('/\D/', '', (string) config('services.correios.remetente.cep')) ?? '';
        $dataPostagem = now('America/Sao_Paulo')->toDateString();
        $produto = (string) config('services.correios.codigo_servico_sedex_12');

        return [
            'idLote' => $idLote,
            'parametrosPrazo' => $itens
                ->map(fn (InterlabLotePostagemItem $item): array => [
                    'coProduto' => $produto,
                    'nuRequisicao' => $item->nu_requisicao,
                    'cepOrigem' => $cepOrigem,
                    'cepDestino' => (string) data_get($item->destinatario, 'endereco.cep'),
                    'dataPostagem' => $dataPostagem,
                ])
                ->values()
                ->all(),
        ];
    }
}

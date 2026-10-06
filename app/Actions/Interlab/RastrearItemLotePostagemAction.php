<?php

namespace App\Actions\Interlab;

use App\Enums\InterlabLotePostagemItemStatus;
use App\Exceptions\CorreiosApiException;
use App\Integrations\Correios\CorreiosLog;
use App\Integrations\Correios\CorreiosRastroClient;
use App\Models\InterlabLotePostagemItem;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Carbon;
use RuntimeException;

class RastrearItemLotePostagemAction
{
    /** @var list<string> */
    private const CODIGOS_ENTREGA = ['BDE', 'BDI', 'BDR'];

    public function __construct(private CorreiosRastroClient $rastro) {}

    /**
     * Consulta o SRO e persiste o snapshot de rastreio do item.
     *
     * @throws CorreiosApiException
     * @throws ConnectionException
     * @throws RuntimeException
     */
    public function execute(InterlabLotePostagemItem $item): InterlabLotePostagemItem
    {
        $item->refresh();

        if (blank($item->codigo_objeto)) {
            throw new RuntimeException('Item sem código de objeto para rastreio.');
        }

        if ($item->status === InterlabLotePostagemItemStatus::Entregue) {
            CorreiosLog::info('rastro.skip_entregue', [
                'item_id' => $item->id,
                'codigo_objeto' => $item->codigo_objeto,
            ]);

            return $item;
        }

        CorreiosLog::info('rastro.inicio', [
            'item_id' => $item->id,
            'codigo_objeto' => $item->codigo_objeto,
            'status_correios_anterior' => $item->status_correios,
        ]);

        $response = $this->rastro->consultar((string) $item->codigo_objeto);

        if ($response->failed()) {
            throw CorreiosApiException::fromResponse($response, CorreiosRastroClient::PATH_OBJETO.'/'.$item->codigo_objeto);
        }

        $objeto = $this->extrairObjeto($response, (string) $item->codigo_objeto);
        $eventos = $objeto['eventos'] ?? [];

        $atributos = [
            'rastreio_payload' => $objeto,
            'rastreado_em' => now(),
        ];

        if (! is_array($eventos) || $eventos === []) {
            $item->update($atributos);

            CorreiosLog::info('rastro.sem_eventos', [
                'item_id' => $item->id,
                'codigo_objeto' => $item->codigo_objeto,
                'mensagem' => $objeto['mensagem'] ?? null,
            ]);

            return $item->fresh();
        }

        $evento = $this->eventoMaisRecente($eventos);
        $codigo = (string) ($evento['codigo'] ?? '');
        $tipo = (string) ($evento['tipo'] ?? '');
        $statusCorreios = $codigo !== '' && $tipo !== '' ? "{$codigo}-{$tipo}" : null;

        $atributos['status_correios'] = $statusCorreios;
        $atributos['ultimo_evento'] = filled($evento['descricao'] ?? null)
            ? mb_substr((string) $evento['descricao'], 0, 191)
            : null;

        if ($this->eEntrega($codigo, $tipo)) {
            $atributos['status'] = InterlabLotePostagemItemStatus::Entregue;
        }

        $item->update($atributos);

        CorreiosLog::info('rastro.persistido', [
            'item_id' => $item->id,
            'codigo_objeto' => $item->codigo_objeto,
            'status_correios' => $statusCorreios,
            'ultimo_evento' => $atributos['ultimo_evento'],
            'entregue' => isset($atributos['status']),
        ]);

        return $item->fresh();
    }

    /**
     * @return array<string, mixed>
     */
    private function extrairObjeto(Response $response, string $codigoObjeto): array
    {
        $json = $response->json();

        if (! is_array($json)) {
            return ['codObjeto' => $codigoObjeto, 'eventos' => []];
        }

        $objetos = $json['objetos'] ?? null;

        if (! is_array($objetos) || $objetos === []) {
            return $json;
        }

        foreach ($objetos as $candidato) {
            if (is_array($candidato) && ($candidato['codObjeto'] ?? null) === $codigoObjeto) {
                return $candidato;
            }
        }

        $primeiro = $objetos[0];

        return is_array($primeiro) ? $primeiro : ['codObjeto' => $codigoObjeto, 'eventos' => []];
    }

    /**
     * @param  list<array<string, mixed>>  $eventos
     * @return array<string, mixed>
     */
    private function eventoMaisRecente(array $eventos): array
    {
        return collect($eventos)
            ->filter(fn (mixed $evento): bool => is_array($evento))
            ->sortByDesc(function (array $evento): int {
                $data = $evento['dtHrCriado'] ?? null;

                if (! is_string($data) || $data === '') {
                    return 0;
                }

                return Carbon::parse($data)->getTimestamp();
            })
            ->first() ?? [];
    }

    private function eEntrega(string $codigo, string $tipo): bool
    {
        return in_array($codigo, self::CODIGOS_ENTREGA, true) && $tipo === '01';
    }
}

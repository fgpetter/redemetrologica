<?php

namespace App\Actions\Interlab;

use App\Enums\InterlabLotePostagemItemStatus;
use App\Enums\InterlabLotePostagemStatus;
use App\Exceptions\CorreiosApiException;
use App\Integrations\Correios\CorreiosLog;
use App\Integrations\Correios\CorreiosPrazoClient;
use App\Models\InterlabLotePostagem;
use App\Models\InterlabLotePostagemItem;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ClassificarServicoItensLotePostagemCorreiosAction
{
    private const JANELA_SEM_PROGRESSO_MINUTOS = 20;

    public function __construct(
        private CorreiosPrazoClient $prazo,
        private EncerrarInterlabLotePostagemComErroAction $encerrar,
    ) {}

    public function execute(InterlabLotePostagem $lote, int $tentativa = 1): ClassificacaoServicoResultado
    {
        $lote->refresh();

        CorreiosLog::info('classificacao.inicio', [
            'lote_id' => $lote->id,
            'lote_uid' => $lote->uid,
            'status' => $lote->status->value,
            'tentativa' => $tentativa,
            'ultima_progressao_em' => $lote->ultima_progressao_em?->toIso8601String(),
        ]);

        if ($lote->status !== InterlabLotePostagemStatus::ClassificandoServico) {
            CorreiosLog::info('classificacao.ignorada', [
                'lote_id' => $lote->id,
                'status' => $lote->status->value,
            ]);

            return ClassificacaoServicoResultado::concluida();
        }

        if ($this->semProgresso($lote)) {
            CorreiosLog::warning('classificacao.sem_progresso', [
                'lote_id' => $lote->id,
                'ultima_progressao_em' => $lote->ultima_progressao_em?->toIso8601String(),
            ]);

            $this->encerrar->execute(
                $lote,
                InterlabLotePostagemStatus::ClassificandoServico->value,
                'A classificação não avançou nos últimos 20 minutos.',
            );

            return ClassificacaoServicoResultado::falhou();
        }

        $cepOrigem = preg_replace('/\D/', '', (string) config('services.correios.remetente.cep')) ?? '';

        if (strlen($cepOrigem) !== 8) {
            $this->encerrar->execute(
                $lote,
                InterlabLotePostagemStatus::ClassificandoServico->value,
                'CEP do remetente não configurado para a consulta de prazo.',
            );

            return ClassificacaoServicoResultado::falhou();
        }

        $pendentes = $lote->itens()
            ->where('status', InterlabLotePostagemItemStatus::AguardandoClassificacao)
            ->orderBy('id')
            ->get();

        if ($pendentes->isEmpty()) {
            CorreiosLog::info('classificacao.sem_pendentes', ['lote_id' => $lote->id]);

            return ClassificacaoServicoResultado::concluida();
        }

        CorreiosLog::info('classificacao.pendentes', [
            'lote_id' => $lote->id,
            'quantidade' => $pendentes->count(),
            'item_ids' => $pendentes->pluck('id')->all(),
        ]);

        foreach ($pendentes->chunk(CorreiosPrazoClient::TAMANHO_LOTE) as $grupo) {
            $resultado = $this->consultarGrupo($lote, $grupo->values(), max(1, $tentativa));

            if ($resultado->estaFalhou() || $resultado->deveRetentar()) {
                return $resultado;
            }
        }

        return ClassificacaoServicoResultado::concluida();
    }

    /**
     * @param  Collection<int, InterlabLotePostagemItem>  $grupo
     */
    private function consultarGrupo(InterlabLotePostagem $lote, Collection $grupo, int $tentativa): ClassificacaoServicoResultado
    {
        $payload = $this->prazo->payload($lote->uid, $grupo);
        $this->registrarRequest($grupo, $payload);

        try {
            $response = $this->prazo->consultarNacional($lote->uid, $grupo);
        } catch (ConnectionException $exception) {
            CorreiosLog::exception('prazo.timeout', $exception, [
                'lote_id' => $lote->id,
                'item_ids' => $grupo->pluck('id')->all(),
            ]);

            return $this->adiar($grupo, $tentativa, null, $exception->getMessage());
        } catch (CorreiosApiException $exception) {
            CorreiosLog::exception('prazo.excecao', $exception, [
                'lote_id' => $lote->id,
                'status' => $exception->status,
                'path' => $exception->path,
                'body' => $exception->body,
            ]);

            if ($this->falhaDefinitiva($exception->status)) {
                $this->registrarEnvelope($grupo, [
                    'http_status' => $exception->status,
                    'corpo' => $exception->body,
                    'tentativa' => $tentativa,
                ]);
                $this->encerrar->execute(
                    $lote,
                    InterlabLotePostagemStatus::ClassificandoServico->value,
                    $exception->getMessage(),
                );

                return ClassificacaoServicoResultado::falhou();
            }

            return $this->adiar($grupo, $tentativa, null, $exception->getMessage());
        }

        if ($response->status() === 429 || $response->serverError()) {
            CorreiosLog::warning('prazo.transitorio', [
                'lote_id' => $lote->id,
                'http_status' => $response->status(),
                'item_ids' => $grupo->pluck('id')->all(),
            ]);

            return $this->adiar($grupo, $tentativa, $response);
        }

        if (in_array($response->status(), [200, 206], true) || $this->temResultadosPorItem($response)) {
            if ($response->status() === 400) {
                CorreiosLog::info('prazo.400_com_itens', [
                    'lote_id' => $lote->id,
                    'item_ids' => $grupo->pluck('id')->all(),
                ]);
            }

            return $this->aplicarResultados($lote, $grupo, $response, $tentativa);
        }

        CorreiosLog::error('prazo.http_definitivo', [
            'lote_id' => $lote->id,
            'http_status' => $response->status(),
            'body' => $response->json() ?? $response->body(),
        ]);
        $this->registrarEnvelope($grupo, $this->envelopeHttp($response, $tentativa));
        $this->encerrar->execute(
            $lote,
            InterlabLotePostagemStatus::ClassificandoServico->value,
            $this->mensagemHttp($response),
        );

        return ClassificacaoServicoResultado::falhou();
    }

    /**
     * @param  Collection<int, InterlabLotePostagemItem>  $grupo
     */
    private function aplicarResultados(
        InterlabLotePostagem $lote,
        Collection $grupo,
        Response $response,
        int $tentativa,
    ): ClassificacaoServicoResultado {
        $porRequisicao = $this->indexarResultados($response);
        $classificouAlgum = false;
        $ausente = false;
        $erroDefinitivo = null;

        foreach ($grupo as $item) {
            $resultado = $porRequisicao[$item->nu_requisicao] ?? null;

            if (! is_array($resultado)) {
                CorreiosLog::warning('classificacao.item_ausente', [
                    'lote_id' => $lote->id,
                    'item_id' => $item->id,
                    'nu_requisicao' => $item->nu_requisicao,
                ]);
                $ausente = true;

                continue;
            }

            $codigoErro = $this->codigoErro($resultado);

            if ($codigoErro === 'PRZ-008') {
                CorreiosLog::info('classificacao.item_sedex', [
                    'lote_id' => $lote->id,
                    'item_id' => $item->id,
                    'nu_requisicao' => $item->nu_requisicao,
                    'tx_erro' => $resultado['txErro'] ?? null,
                ]);
                $this->classificar($item, (string) config('services.correios.codigo_servico_sedex'), $resultado, 'PRZ-008');
                $classificouAlgum = true;

                continue;
            }

            if ($codigoErro !== null) {
                CorreiosLog::error('classificacao.item_tx_erro', [
                    'lote_id' => $lote->id,
                    'item_id' => $item->id,
                    'nu_requisicao' => $item->nu_requisicao,
                    'codigo_erro' => $codigoErro,
                    'resultado' => $resultado,
                ]);
                $item->update(['consulta_prazo_response' => $resultado]);
                $erroDefinitivo = "A API Prazo recusou o trecho ({$codigoErro}).";

                continue;
            }

            if ($this->temPrazoEntrega($resultado)) {
                CorreiosLog::info('classificacao.item_sedex_12', [
                    'lote_id' => $lote->id,
                    'item_id' => $item->id,
                    'nu_requisicao' => $item->nu_requisicao,
                    'prazo_entrega' => $resultado['prazoEntrega'] ?? null,
                ]);
                $this->classificar($item, (string) config('services.correios.codigo_servico_sedex_12'), $resultado, null);
                $classificouAlgum = true;

                continue;
            }

            $ausente = true;
            CorreiosLog::warning('classificacao.item_sem_prazo', [
                'lote_id' => $lote->id,
                'item_id' => $item->id,
                'nu_requisicao' => $item->nu_requisicao,
                'resultado' => $resultado,
            ]);
        }

        if ($classificouAlgum) {
            $lote->update(['ultima_progressao_em' => now()]);
        }

        if ($erroDefinitivo !== null) {
            $this->encerrar->execute(
                $lote,
                InterlabLotePostagemStatus::ClassificandoServico->value,
                $erroDefinitivo,
            );

            return ClassificacaoServicoResultado::falhou();
        }

        if ($ausente) {
            $atraso = $this->atraso($tentativa, $response);
            $this->registrarEnvelope(
                $grupo->filter(fn (InterlabLotePostagemItem $item): bool => $item->status === InterlabLotePostagemItemStatus::AguardandoClassificacao),
                [
                    ...$this->envelopeHttp($response, $tentativa),
                    'proxima_tentativa_em' => now()->addSeconds($atraso)->toIso8601String(),
                ],
            );

            return ClassificacaoServicoResultado::retentar($atraso);
        }

        return ClassificacaoServicoResultado::concluida();
    }

    /**
     * @param  Collection<int, InterlabLotePostagemItem>  $grupo
     */
    private function adiar(
        Collection $grupo,
        int $tentativa,
        ?Response $response,
        ?string $mensagem = null,
    ): ClassificacaoServicoResultado {
        $atraso = $this->atraso($tentativa, $response);
        $envelope = $response === null
            ? [
                'erro' => 'timeout',
                'mensagem' => $mensagem,
                'tentativa' => $tentativa,
            ]
            : $this->envelopeHttp($response, $tentativa);

        $envelope['proxima_tentativa_em'] = now()->addSeconds($atraso)->toIso8601String();
        $this->registrarEnvelope($grupo, $envelope);

        CorreiosLog::warning('classificacao.adiada', [
            'item_ids' => $grupo->pluck('id')->all(),
            'tentativa' => $tentativa,
            'atraso_segundos' => $atraso,
            'mensagem' => $mensagem,
            'http_status' => $response?->status(),
        ]);

        return ClassificacaoServicoResultado::retentar($atraso);
    }

    /**
     * @param  Collection<int, InterlabLotePostagemItem>  $grupo
     * @param  array{idLote: string, parametrosPrazo: list<array<string, string>>}  $payload
     */
    private function registrarRequest(Collection $grupo, array $payload): void
    {
        $porRequisicao = collect($payload['parametrosPrazo'])->keyBy('nuRequisicao');

        foreach ($grupo as $item) {
            $item->update([
                'consulta_prazo_request' => $porRequisicao->get($item->nu_requisicao),
            ]);
        }
    }

    /**
     * @param  Collection<int, InterlabLotePostagemItem>  $grupo
     * @param  array<string, mixed>  $envelope
     */
    private function registrarEnvelope(Collection $grupo, array $envelope): void
    {
        foreach ($grupo as $item) {
            if ($item->status !== InterlabLotePostagemItemStatus::AguardandoClassificacao) {
                continue;
            }

            $item->update(['consulta_prazo_response' => $envelope]);
        }
    }

    /**
     * @param  array<string, mixed>  $resultado
     */
    private function classificar(
        InterlabLotePostagemItem $item,
        string $codigoServico,
        array $resultado,
        ?string $motivo,
    ): void {
        $item->update([
            'codigo_servico' => $codigoServico,
            'status' => InterlabLotePostagemItemStatus::Classificado,
            'consulta_prazo_response' => $resultado,
            'consulta_prazo_em' => now(),
            'motivo_fallback_servico' => $motivo,
            'erro_mensagem' => null,
        ]);
    }

    private function semProgresso(InterlabLotePostagem $lote): bool
    {
        if ($lote->ultima_progressao_em === null) {
            return false;
        }

        return $lote->ultima_progressao_em->lte(now()->subMinutes(self::JANELA_SEM_PROGRESSO_MINUTOS));
    }

    private function falhaDefinitiva(int $status): bool
    {
        return $status >= 400 && $status < 500 && $status !== 429;
    }

    private function atraso(int $tentativa, ?Response $response = null): int
    {
        $exponencial = 2 ** max(0, $tentativa - 1);

        return (int) max($exponencial, $this->retryAfterSegundos($response) ?? 0, 1);
    }

    private function retryAfterSegundos(?Response $response): ?int
    {
        if ($response === null) {
            return null;
        }

        $header = $response->header('Retry-After');

        if ($header === '') {
            return null;
        }

        if (is_numeric($header)) {
            return (int) $header;
        }

        $quando = Carbon::parse($header);

        return max(0, $quando->getTimestamp() - now()->getTimestamp());
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function indexarResultados(Response $response): array
    {
        $indexados = [];

        foreach ($this->listaDeResultados($response) as $resultado) {
            if (! is_array($resultado)) {
                continue;
            }

            $chave = (string) ($resultado['nuRequisicao'] ?? '');

            if ($chave === '') {
                continue;
            }

            $indexados[$chave] = $resultado;
        }

        return $indexados;
    }

    /**
     * @return list<mixed>
     */
    private function listaDeResultados(Response $response): array
    {
        $json = $response->json();

        if (! is_array($json)) {
            return [];
        }

        if (array_is_list($json)) {
            return $json;
        }

        foreach (['parametrosPrazo', 'resultados', 'itens'] as $chave) {
            if (isset($json[$chave]) && is_array($json[$chave]) && array_is_list($json[$chave])) {
                return $json[$chave];
            }
        }

        if (isset($json['nuRequisicao'])) {
            return [$json];
        }

        return [];
    }

    private function temResultadosPorItem(Response $response): bool
    {
        foreach ($this->listaDeResultados($response) as $resultado) {
            if (is_array($resultado) && filled($resultado['nuRequisicao'] ?? null)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $resultado
     */
    private function codigoErro(array $resultado): ?string
    {
        $txErro = $resultado['txErro'] ?? null;

        if (is_string($txErro) && $txErro !== '') {
            return $this->normalizarCodigoErro($txErro);
        }

        if (is_array($txErro)) {
            $codigo = (string) ($txErro['codigo'] ?? $txErro['txErro'] ?? '');

            return $codigo !== '' ? $this->normalizarCodigoErro($codigo) : null;
        }

        return null;
    }

    private function normalizarCodigoErro(string $txErro): string
    {
        if (preg_match('/^(PRZ-\d+)/', $txErro, $matches) === 1) {
            return $matches[1];
        }

        return $txErro;
    }

    /**
     * @param  array<string, mixed>  $resultado
     */
    private function temPrazoEntrega(array $resultado): bool
    {
        return array_key_exists('prazoEntrega', $resultado)
            && $resultado['prazoEntrega'] !== null
            && $resultado['prazoEntrega'] !== '';
    }

    /**
     * @return array<string, mixed>
     */
    private function envelopeHttp(Response $response, int $tentativa): array
    {
        $json = $response->json();

        return [
            'http_status' => $response->status(),
            'corpo' => is_array($json) ? $json : $response->body(),
            'tentativa' => $tentativa,
        ];
    }

    private function mensagemHttp(Response $response): string
    {
        $json = $response->json();
        $detalhe = is_array($json)
            ? ($json['msgs'][0] ?? $json['mensagem'] ?? $json['causa'] ?? $response->body())
            : $response->body();

        if (is_array($detalhe)) {
            $detalhe = $detalhe['mensagem'] ?? json_encode($detalhe);
        }

        return 'HTTP '.$response->status().' na consulta de prazo: '.mb_substr((string) $detalhe, 0, 500);
    }
}

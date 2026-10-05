<?php

namespace App\Actions\Interlab;

use App\Enums\CorreiosFormatoObjeto;
use App\Enums\CorreiosPrePostagemStatus;
use App\Enums\InterlabLotePostagemItemStatus;
use App\Enums\InterlabLotePostagemStatus;
use App\Exceptions\CorreiosApiException;
use App\Integrations\Correios\CorreiosLog;
use App\Integrations\Correios\CorreiosPrePostagemClient;
use App\Models\InterlabLotePostagem;
use App\Models\InterlabLotePostagemItem;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Sleep;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CriarPrePostagemItemLoteCorreiosAction
{
    private const POLL_TENTATIVAS = 5;

    private const POLL_INTERVALO_INICIAL_SEGUNDOS = 5;

    public function __construct(
        private CorreiosPrePostagemClient $prePostagem,
        private DestinatarioInterlabSnapshot $destinatario,
        private EncerrarInterlabLotePostagemComErroAction $encerrar,
    ) {}

    public function execute(InterlabLotePostagemItem $item, int $tentativa = 1): ResultadoEtapaLotePostagem
    {
        $item->refresh();
        $lote = $item->lotePostagem()->first();

        if ($lote === null) {
            return ResultadoEtapaLotePostagem::falhou();
        }

        CorreiosLog::info('prepostagem.inicio', [
            'lote_id' => $lote->id,
            'lote_uid' => $lote->uid,
            'item_id' => $item->id,
            'status_item' => $item->status->value,
            'codigo_servico' => $item->codigo_servico,
            'status_atual' => $item->status_atual?->value,
        ]);

        if ($item->id_prepostagem !== null && $item->id_prepostagem !== '') {
            return $this->retomarPrePostagemExistente($lote, $item, $tentativa);
        }

        if ($item->status !== InterlabLotePostagemItemStatus::Classificado || blank($item->codigo_servico)) {
            $this->encerrar->execute(
                $lote,
                InterlabLotePostagemStatus::GerandoPrePostagens->value,
                'Item sem classificação válida para criar pré-postagem.',
            );

            return ResultadoEtapaLotePostagem::falhou();
        }

        try {
            $this->validarAntesDaApi($lote, $item);
            $payload = $this->montarPayload($lote, $item);
        } catch (ValidationException $exception) {
            $mensagem = collect($exception->errors())->flatten()->first() ?: 'Dados inválidos para pré-postagem.';
            CorreiosLog::error('prepostagem.validacao', [
                'lote_id' => $lote->id,
                'item_id' => $item->id,
                'mensagem' => $mensagem,
            ]);
            $this->encerrar->execute(
                $lote,
                InterlabLotePostagemStatus::GerandoPrePostagens->value,
                (string) $mensagem,
            );

            return ResultadoEtapaLotePostagem::falhou();
        }

        CorreiosLog::info('prepostagem.payload', [
            'lote_id' => $lote->id,
            'item_id' => $item->id,
            'payload' => $payload,
            'nome_truncado' => $payload['destinatario']['nome'] ?? null,
            'nome_snapshot' => data_get($item->destinatario, 'nome'),
        ]);

        try {
            $response = $this->prePostagem->criar($payload);
        } catch (ConnectionException $exception) {
            CorreiosLog::exception('prepostagem.timeout', $exception, [
                'lote_id' => $lote->id,
                'item_id' => $item->id,
            ]);

            return ResultadoEtapaLotePostagem::retentar(1);
        }

        return $this->interpretarRespostaCriacao($lote, $item, $response, $tentativa);
    }

    private function retomarPrePostagemExistente(
        InterlabLotePostagem $lote,
        InterlabLotePostagemItem $item,
        int $tentativa,
    ): ResultadoEtapaLotePostagem {
        CorreiosLog::info('prepostagem.skip', [
            'lote_id' => $lote->id,
            'item_id' => $item->id,
            'id_prepostagem' => $item->id_prepostagem,
            'status_atual' => $item->status_atual?->value,
            'motivo' => 'ja_tem_id',
        ]);

        if ($item->status_atual === CorreiosPrePostagemStatus::Prepostado) {
            if ($item->status !== InterlabLotePostagemItemStatus::Prepostado) {
                $item->update(['status' => InterlabLotePostagemItemStatus::Prepostado]);
            }

            return ResultadoEtapaLotePostagem::concluida();
        }

        if ($this->statusEhTerminal($item->status_atual?->value)) {
            $this->encerrar->execute(
                $lote,
                InterlabLotePostagemStatus::GerandoPrePostagens->value,
                'A pré-postagem retornou status terminal '.$item->status_atual?->value.'.',
            );

            return ResultadoEtapaLotePostagem::falhou();
        }

        return $this->aguardarStatusPrepostado($lote, $item, $tentativa);
    }

    private function interpretarRespostaCriacao(
        InterlabLotePostagem $lote,
        InterlabLotePostagemItem $item,
        Response $response,
        int $tentativa,
    ): ResultadoEtapaLotePostagem {
        $status = $response->status();
        $json = $response->json();
        $chaves = is_array($json) ? array_keys($json) : [];

        if ($status === 429 || $status >= 500) {
            CorreiosLog::warning('prepostagem.transitorio', [
                'lote_id' => $lote->id,
                'item_id' => $item->id,
                'http' => $status,
                'chaves' => $chaves,
                'retry_after' => $response->header('Retry-After'),
            ]);

            return ResultadoEtapaLotePostagem::retentar($this->atrasoHttp($response, 1));
        }

        if ($status >= 400) {
            CorreiosLog::error('prepostagem.http_definitivo', [
                'lote_id' => $lote->id,
                'item_id' => $item->id,
                'http' => $status,
                'chaves' => $chaves,
                'body' => is_array($json) ? $json : $response->body(),
            ]);

            $exception = CorreiosApiException::fromResponse($response, CorreiosPrePostagemClient::PATH_CRIAR);
            $this->encerrar->execute(
                $lote,
                InterlabLotePostagemStatus::GerandoPrePostagens->value,
                $exception->getMessage(),
            );

            return ResultadoEtapaLotePostagem::falhou();
        }

        $id = is_array($json) ? ($json['id'] ?? null) : null;
        $codigoObjeto = is_array($json) ? ($json['codigoObjeto'] ?? null) : null;
        $statusAtualBruto = is_array($json) ? ($json['statusAtual'] ?? null) : null;
        $statusAtual = is_numeric($statusAtualBruto) ? (int) $statusAtualBruto : null;

        if (blank($id) || blank($codigoObjeto) || $statusAtual === null) {
            CorreiosLog::error('prepostagem.status_inesperado', [
                'lote_id' => $lote->id,
                'item_id' => $item->id,
                'http' => $status,
                'chaves' => $chaves,
                'statusAtual' => $statusAtualBruto,
                'id' => $id,
                'codigoObjeto' => $codigoObjeto,
            ]);

            $this->encerrar->execute(
                $lote,
                InterlabLotePostagemStatus::GerandoPrePostagens->value,
                'A pré-postagem não retornou id, codigoObjeto ou statusAtual.',
            );

            return ResultadoEtapaLotePostagem::falhou();
        }

        $item->update([
            'id_prepostagem' => (string) $id,
            'codigo_objeto' => (string) $codigoObjeto,
            'status_atual' => $statusAtual,
        ]);

        if ($statusAtual === CorreiosPrePostagemStatus::Prepostado->value) {
            return $this->marcarPrepostado($lote, $item->fresh(), (string) $id, (string) $codigoObjeto, $statusAtual);
        }

        if ($this->statusEhTransitorio($statusAtual)) {
            CorreiosLog::info('prepostagem.pendente', [
                'lote_id' => $lote->id,
                'item_id' => $item->id,
                'id_prepostagem' => $id,
                'codigo_objeto' => $codigoObjeto,
                'statusAtual' => $statusAtualBruto,
                'chaves' => $chaves,
            ]);

            return $this->aguardarStatusPrepostado($lote, $item->fresh(), $tentativa);
        }

        $motivoCancelamento = is_array($json) ? ($json['motivoCancelamento'] ?? null) : null;

        CorreiosLog::error('prepostagem.status_inesperado', [
            'lote_id' => $lote->id,
            'item_id' => $item->id,
            'http' => $status,
            'chaves' => $chaves,
            'statusAtual' => $statusAtualBruto,
            'id' => $id,
            'codigoObjeto' => $codigoObjeto,
            'emiteDCe' => is_array($json) ? ($json['emiteDCe'] ?? null) : null,
            'motivoCancelamento' => $motivoCancelamento,
        ]);

        $this->encerrar->execute(
            $lote,
            InterlabLotePostagemStatus::GerandoPrePostagens->value,
            $this->mensagemComMotivoCancelamento(
                'A pré-postagem retornou statusAtual='.$statusAtual.' (esperado 2 ou transitório 1/7).',
                $motivoCancelamento,
            ),
        );

        return ResultadoEtapaLotePostagem::falhou();
    }

    private function aguardarStatusPrepostado(
        InterlabLotePostagem $lote,
        InterlabLotePostagemItem $item,
        int $tentativa,
    ): ResultadoEtapaLotePostagem {
        for ($poll = 1; $poll <= self::POLL_TENTATIVAS; $poll++) {
            $intervalo = self::POLL_INTERVALO_INICIAL_SEGUNDOS + $poll - 1;
            Sleep::for($intervalo)->seconds();

            try {
                $response = $this->prePostagem->consultar((string) $item->id_prepostagem);
            } catch (ConnectionException $exception) {
                CorreiosLog::exception('prepostagem.timeout_consulta', $exception, [
                    'lote_id' => $lote->id,
                    'item_id' => $item->id,
                    'poll' => $poll,
                ]);

                return ResultadoEtapaLotePostagem::retentar(1);
            }

            $status = $response->status();
            $json = $response->json();
            $chaves = is_array($json) ? array_keys($json) : [];
            $consultada = $this->extrairPrePostagemConsultada(is_array($json) ? $json : null);
            $statusAtualBruto = $consultada['statusAtual'] ?? null;
            $statusAtual = is_numeric($statusAtualBruto) ? (int) $statusAtualBruto : null;
            $codigoObjeto = $consultada['codigoObjeto'] ?? $item->codigo_objeto;

            CorreiosLog::info('prepostagem.poll', [
                'lote_id' => $lote->id,
                'item_id' => $item->id,
                'id_prepostagem' => $item->id_prepostagem,
                'tentativa' => $poll,
                'intervalo_segundos' => $intervalo,
                'http' => $status,
                'chaves' => $chaves,
                'statusAtual' => $statusAtualBruto,
            ]);

            if ($status === 429 || $status >= 500) {
                CorreiosLog::warning('prepostagem.transitorio', [
                    'lote_id' => $lote->id,
                    'item_id' => $item->id,
                    'http' => $status,
                    'poll' => $poll,
                    'retry_after' => $response->header('Retry-After'),
                ]);

                return ResultadoEtapaLotePostagem::retentar($this->atrasoHttp($response, $tentativa));
            }

            if ($status >= 400) {
                CorreiosLog::error('prepostagem.http_definitivo', [
                    'lote_id' => $lote->id,
                    'item_id' => $item->id,
                    'http' => $status,
                    'poll' => $poll,
                    'chaves' => $chaves,
                    'body' => is_array($json) ? $json : $response->body(),
                ]);

                $exception = CorreiosApiException::fromResponse($response, CorreiosPrePostagemClient::PATH_CONSULTAR);
                $this->encerrar->execute(
                    $lote,
                    InterlabLotePostagemStatus::GerandoPrePostagens->value,
                    $exception->getMessage(),
                );

                return ResultadoEtapaLotePostagem::falhou();
            }

            if ($statusAtual !== null) {
                $item->update([
                    'status_atual' => $statusAtual,
                    'codigo_objeto' => filled($codigoObjeto) ? (string) $codigoObjeto : $item->codigo_objeto,
                ]);
            }

            if ($statusAtual === CorreiosPrePostagemStatus::Prepostado->value) {
                return $this->marcarPrepostado(
                    $lote,
                    $item->fresh(),
                    (string) $item->id_prepostagem,
                    (string) ($codigoObjeto ?: $item->codigo_objeto),
                    $statusAtual,
                );
            }

            if ($this->statusEhTerminal($statusAtual)) {
                $motivoCancelamento = $consultada['motivoCancelamento'] ?? null;

                CorreiosLog::error('prepostagem.status_terminal', [
                    'lote_id' => $lote->id,
                    'item_id' => $item->id,
                    'statusAtual' => $statusAtualBruto,
                    'chaves' => $chaves,
                    'emiteDCe' => $consultada['emiteDCe'] ?? null,
                    'motivoCancelamento' => $motivoCancelamento,
                ]);

                $this->encerrar->execute(
                    $lote,
                    InterlabLotePostagemStatus::GerandoPrePostagens->value,
                    $this->mensagemComMotivoCancelamento(
                        'A pré-postagem retornou status terminal '.$statusAtual.'.',
                        $motivoCancelamento,
                    ),
                );

                return ResultadoEtapaLotePostagem::falhou();
            }

            if ($statusAtual !== null && ! $this->statusEhTransitorio($statusAtual)) {
                CorreiosLog::error('prepostagem.status_inesperado', [
                    'lote_id' => $lote->id,
                    'item_id' => $item->id,
                    'http' => $status,
                    'chaves' => $chaves,
                    'statusAtual' => $statusAtualBruto,
                    'id' => $item->id_prepostagem,
                    'codigoObjeto' => $codigoObjeto,
                ]);

                $this->encerrar->execute(
                    $lote,
                    InterlabLotePostagemStatus::GerandoPrePostagens->value,
                    'A pré-postagem retornou statusAtual='.$statusAtual.' na consulta.',
                );

                return ResultadoEtapaLotePostagem::falhou();
            }
        }

        CorreiosLog::warning('prepostagem.poll_esgotado', [
            'lote_id' => $lote->id,
            'item_id' => $item->id,
            'id_prepostagem' => $item->id_prepostagem,
            'status_atual' => $item->fresh()->status_atual?->value,
        ]);

        return ResultadoEtapaLotePostagem::retentar($this->atrasoPoll($tentativa));
    }

    private function marcarPrepostado(
        InterlabLotePostagem $lote,
        InterlabLotePostagemItem $item,
        string $id,
        string $codigoObjeto,
        int $statusAtual,
    ): ResultadoEtapaLotePostagem {
        $item->update([
            'id_prepostagem' => $id,
            'codigo_objeto' => $codigoObjeto,
            'status_atual' => CorreiosPrePostagemStatus::Prepostado,
            'status' => InterlabLotePostagemItemStatus::Prepostado,
            'erro_mensagem' => null,
        ]);

        $lote->update(['ultima_progressao_em' => now()]);

        CorreiosLog::info('prepostagem.item_ok', [
            'lote_id' => $lote->id,
            'item_id' => $item->id,
            'id_prepostagem' => $id,
            'codigo_objeto' => $codigoObjeto,
            'statusAtual' => $statusAtual,
            'status_persistido' => InterlabLotePostagemItemStatus::Prepostado->value,
        ]);

        return ResultadoEtapaLotePostagem::concluida();
    }

    /**
     * @param  array<string, mixed>  $json
     * @return array<string, mixed>|null
     */
    private function extrairPrePostagemConsultada(array $json): ?array
    {
        $itens = $json['itens'] ?? null;

        if (is_array($itens) && isset($itens[0]) && is_array($itens[0])) {
            return $itens[0];
        }

        if (isset($json['id']) || isset($json['statusAtual'])) {
            return $json;
        }

        return null;
    }

    private function mensagemComMotivoCancelamento(string $mensagem, mixed $motivoCancelamento): string
    {
        if (! is_string($motivoCancelamento) || blank($motivoCancelamento)) {
            return $mensagem;
        }

        return $mensagem.' Motivo: '.$motivoCancelamento.'.';
    }

    private function statusEhTransitorio(?int $statusAtual): bool
    {
        return in_array($statusAtual, [
            CorreiosPrePostagemStatus::Preatendido->value,
            CorreiosPrePostagemStatus::Pendente->value,
        ], true);
    }

    private function statusEhTerminal(?int $statusAtual): bool
    {
        return in_array($statusAtual, [
            CorreiosPrePostagemStatus::Expirado->value,
            CorreiosPrePostagemStatus::Cancelado->value,
            CorreiosPrePostagemStatus::Estornado->value,
        ], true);
    }

    /**
     * @return array<string, mixed>
     */
    private function montarPayload(InterlabLotePostagem $lote, InterlabLotePostagemItem $item): array
    {
        $remetenteConfig = config('services.correios.remetente');
        $remetente = [
            'nome' => (string) ($remetenteConfig['nome'] ?? ''),
            'cpfCnpj' => preg_replace('/\D/', '', (string) ($remetenteConfig['cpf_cnpj'] ?? '')) ?? '',
            'endereco' => [
                'cep' => preg_replace('/\D/', '', (string) ($remetenteConfig['cep'] ?? '')) ?? '',
                'logradouro' => (string) ($remetenteConfig['logradouro'] ?? ''),
                'numero' => (string) ($remetenteConfig['numero'] ?? ''),
                'bairro' => (string) ($remetenteConfig['bairro'] ?? ''),
                'cidade' => (string) ($remetenteConfig['cidade'] ?? ''),
                'uf' => (string) ($remetenteConfig['uf'] ?? ''),
            ],
        ];

        if (filled($remetenteConfig['complemento'] ?? null)) {
            $remetente['endereco']['complemento'] = (string) $remetenteConfig['complemento'];
        }

        $payload = [
            'numeroCartaoPostagem' => (string) config('services.correios.cartao_postagem'),
            'codigoServico' => (string) $item->codigo_servico,
            'pesoInformado' => (string) $lote->peso_gramas,
            'codigoFormatoObjetoInformado' => $lote->codigo_formato->value,
            'cienteObjetoNaoProibido' => '1',
            'emiteDCe' => 'S',
            'itensDeclaracaoConteudo' => $lote->declaracao_conteudo,
            'remetente' => $remetente,
            'destinatario' => $this->destinatario->paraPrePostagem($item->destinatario),
        ];

        if ($lote->codigo_formato === CorreiosFormatoObjeto::CaixaPacote) {
            $payload['alturaInformada'] = (string) $lote->altura;
            $payload['larguraInformada'] = (string) $lote->largura;
            $payload['comprimentoInformado'] = (string) $lote->comprimento;
        }

        if ($lote->codigo_formato === CorreiosFormatoObjeto::CilindroRolo) {
            $payload['diametroInformado'] = (string) $lote->diametro;
        }

        return $payload;
    }

    private function validarAntesDaApi(InterlabLotePostagem $lote, InterlabLotePostagemItem $item): void
    {
        $mensagens = [];

        if ($lote->peso_gramas < 1) {
            $mensagens[] = 'O peso deve ser maior que zero.';
        }

        if ($lote->codigo_formato === CorreiosFormatoObjeto::CaixaPacote) {
            foreach (['altura' => $lote->altura, 'largura' => $lote->largura, 'comprimento' => $lote->comprimento] as $campo => $valor) {
                if (! is_string($valor) || preg_match('/^\d{1,3}$/', $valor) !== 1) {
                    $mensagens[] = "Dimensão {$campo} inválida para a pré-postagem.";
                }
            }
        }

        if ($lote->codigo_formato === CorreiosFormatoObjeto::CilindroRolo
            && (! is_string($lote->diametro) || preg_match('/^\d{1,3}$/', $lote->diametro) !== 1)) {
            $mensagens[] = 'Diâmetro inválido para a pré-postagem.';
        }

        $declaracao = $lote->declaracao_conteudo;

        if (! is_array($declaracao) || $declaracao === []) {
            $mensagens[] = 'Informe ao menos um item da declaração de conteúdo.';
        } else {
            foreach ($declaracao as $indice => $linha) {
                $conteudo = (string) ($linha['conteudo'] ?? '');
                if (Str::length($conteudo) < 5) {
                    $mensagens[] = 'O conteúdo do item '.($indice + 1).' deve ter no mínimo 5 caracteres.';
                }
                if (blank($linha['quantidade'] ?? null)) {
                    $mensagens[] = 'Informe a quantidade do item '.($indice + 1).' da declaração.';
                }
                if (blank($linha['valor'] ?? null)) {
                    $mensagens[] = 'Informe o valor unitário do item '.($indice + 1).' da declaração.';
                }
            }
        }

        $destinatario = $item->destinatario;
        $nome = (string) data_get($destinatario, 'nome');
        if (Str::length($nome) < 3) {
            $mensagens[] = 'O nome do destinatário é inválido.';
        }

        $cep = preg_replace('/\D/', '', (string) data_get($destinatario, 'endereco.cep')) ?? '';
        if (strlen($cep) !== 8) {
            $mensagens[] = 'O CEP do destinatário deve ter 8 dígitos.';
        }

        foreach (['logradouro', 'numero', 'bairro', 'cidade', 'uf'] as $campo) {
            if (blank(data_get($destinatario, "endereco.{$campo}"))) {
                $mensagens[] = "O endereço do destinatário está incompleto ({$campo}).";
            }
        }

        if ($mensagens !== []) {
            throw ValidationException::withMessages(['prepostagem' => $mensagens]);
        }
    }

    private function atrasoHttp(Response $response, int $tentativa): int
    {
        $backoff = (int) (2 ** max(0, $tentativa - 1));
        $retryAfter = (int) $response->header('Retry-After');

        return max($backoff, $retryAfter, self::POLL_INTERVALO_INICIAL_SEGUNDOS);
    }

    private function atrasoPoll(int $tentativa): int
    {
        return self::POLL_INTERVALO_INICIAL_SEGUNDOS + max(0, $tentativa - 1);
    }
}

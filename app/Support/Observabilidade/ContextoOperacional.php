<?php

namespace App\Support\Observabilidade;

/** Lista positiva: valores livres e objetos não fazem parte da telemetria operacional. */
final class ContextoOperacional
{
    public static function selecionar(array $contexto): array
    {
        $seguro = [];
        foreach ($contexto as $chave => $valor) {
            if (preg_match('/senha|token|secret|authorization|email|cpf|cnpj|telefone|endereco|body|payload|content|codigo_acesso/i', (string) $chave)) {
                continue;
            }
            if (in_array($chave, ['etapas', 'metricas'], true) && is_array($valor)) {
                $seguro[$chave] = $chave === 'etapas'
                    ? array_map(self::selecionar(...), $valor)
                    : array_filter($valor, fn ($item) => is_int($item) || is_float($item));
            } elseif (preg_match('/(^|_)(uid|id)$/', (string) $chave) && (is_int($valor) || (is_string($valor) && preg_match('/^[a-zA-Z0-9_-]{1,80}$/D', $valor)))) {
                $seguro[$chave] = $valor;
            } elseif (in_array($chave, ['evento', 'fluxo', 'etapa', 'resultado', 'motivo', 'tipo', 'status', 'de', 'para', 'method'], true)
                && is_string($valor) && preg_match('/^[a-zA-Z0-9_.:-]{1,120}$/D', $valor)) {
                $seguro[$chave] = $valor;
            } elseif (in_array($chave, ['status_http', 'http', 'tentativa', 'duracao_ms', 'atraso_segundos', 'quantidade', 'bytes', 'pdf_bytes', 'execucoes', 'itens', 'concluida', 'retentar', 'falhou', 'responsavel_tecnico_valido', 'pessoa_valida', 'pendencia_liberada', 'simulacao'], true)
                && (is_int($valor) || is_float($valor) || is_bool($valor) || $valor === null)) {
                $seguro[$chave] = $valor;
            }
        }

        return $seguro;
    }
}

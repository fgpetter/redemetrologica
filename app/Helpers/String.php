<?php

/**
 * Remove caracteres especiais, remove acentos e espaços
 *
 * @param  string  $file_name
 */
function sanitizeFileName($file_name): string
{
    return preg_replace(
        '/[^A-Za-z0-9\-]/',
        '',
        str_replace(
            ' ',
            '-',
            preg_replace(
                '/&([a-z])[a-z]+;/i',
                '$1',
                htmlentities(
                    trim($file_name)
                )
            )
        )
    );
}

/**
 * Retorna apenas parte do email e domínio
 *
 * @param  array  $array
 * @return string|null
 */
function obfuscateEmail($email)
{
    $em = explode('@', $email);
    $name = implode('@', array_slice($em, 0, count($em) - 1));
    $len = floor(strlen($name) / 2);

    return substr($name, 0, $len).str_repeat('*', $len).'@'.end($em);
}

function isInvalidEmail($email)
{
    if (preg_match('/^[0-9a-z]([-_.]*?[0-9a-z])*@[0-9a-z]([-.]?[0-9a-z])*\\.[a-z]{2,9}$/', $email, $matches) == 0) {
        return true;
    }

    return false;
}

/**
 * Retorna apenas os números de uma string
 *
 * @return string|array|null
 */
function return_only_nunbers($data)
{
    if (is_string($data)) {
        return preg_replace('/[^0-9]/', '', $data);
    }

    if (is_array($data)) {
        $sanitized = [];
        foreach ($data as $key => $value) {
            $sanitized[$key] = preg_replace('/[^0-9]/', '', $value);
        }

        return $sanitized;
    }

    return null;
}

/**
 * Formata valor para decimal padrão SQL
 *
 * @param  string  $valor
 */
function formataMoeda($valor): ?string
{
    if ($valor) {
        if (str_contains($valor, '.') && str_contains($valor, ',')) {
            return str_replace(',', '.', str_replace('.', '', $valor));
        }

        if (str_contains($valor, '.') && ! str_contains($valor, ',')) {
            return substr_count($valor, '.') > 1 ? str_replace('.', '', $valor) : $valor;
        }

        if (str_contains($valor, ',') && ! str_contains($valor, '.')) {
            return str_replace(',', '.', $valor);
        }

        return $valor;

    } else {
        return null;
    }
}

/**
 * Formata valor monetário para exibição em texto (padrão brasileiro).
 */
function formataValorBr(float|int|string|null $valor): string
{
    return number_format((float) $valor, 2, ',', '.');
}

/**
 * Linha de observação de lançamento gerado por inscrição em curso.
 */
function linhaObservacaoInscricao($nome, $valor, $dataHora = null): string
{
    $dataFormatada = $dataHora ?? now()->format('d/m/Y H:i');

    return 'Inscrição de '.$nome.', com valor de R$ '.formataValorBr($valor).', em '.$dataFormatada." \n";
}

/**
 * Linha de observação de lançamento gerado por inscrição em interlab.
 */
function linhaObservacaoInscricaoInterlab($nome_laboratorio, $valor): string
{
    return 'Inscrição de '.$nome_laboratorio.', com valor de R$ '.formataValorBr($valor)." \n";
}

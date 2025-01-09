<?php

/**
 * Remove caracteres especiais, remove acentos e espaços
 *
 * @param string $file_name
 * @return string
 */
function sanitizeFileName($file_name): string
{
  return preg_replace(
    '/[^A-Za-z0-9\-]/',
    '',
    str_replace(
      " ",
      "-",
      preg_replace(
        "/&([a-z])[a-z]+;/i",
        "$1",
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
 * @param array $array
 * @return string|null
 */

function obfuscate_email($email)
{
    $em   = explode("@",$email);
    $name = implode('@', array_slice($em, 0, count($em)-1));
    $len  = floor(strlen($name)/2);

    return substr($name,0, $len) . str_repeat('*', $len) . "@" . end($em);   
}

/**
 * Retorna apenas números de uma string
 *
 * @param array $array
 * @return string|null
 */

function return_only_nunbers(array $array)
{
  $sanitized = [];
  foreach ($array as $key => $value) {
    $sanitized[$key] = preg_replace("/[^0-9]/", "", $value);
  }
  return $sanitized;
}

/**
 * Formata valor para decimal padrão SQL
 *
 * @param string $valor
 * @return string|null
 */
function formataMoeda($valor): ?string
{
  if ($valor) {
    if(str_contains($valor, '.') && str_contains($valor, ',') ) {
      return str_replace(',', '.', str_replace('.', '', $valor));
    }

    if(str_contains($valor, '.') && !str_contains($valor, ',') ) {
      return $valor;
    }

    if(str_contains($valor, ',') && !str_contains($valor, '.') ){
      return str_replace(',', '.', $valor);
    }

  } else {
    return null;
  }
}

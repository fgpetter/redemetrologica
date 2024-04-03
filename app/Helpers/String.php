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

function obfuscate_email($email)
{
    $em   = explode("@",$email);
    $name = implode('@', array_slice($em, 0, count($em)-1));
    $len  = floor(strlen($name)/2);

    return substr($name,0, $len) . str_repeat('*', $len) . "@" . end($em);   
}
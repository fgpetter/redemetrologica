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
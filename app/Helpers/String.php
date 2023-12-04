<?php

/**
 * Remove caracteres especiais, remove acentos e espaços
 *
 * @param string $file_name
 * @return string
 */
function sanitizeFileName($file_name): string
{
  return str_replace(
    " ",
    "_",
    preg_replace(
      "/&([a-z])[a-z]+;/i", 
      "$1", 
      htmlentities(
        trim($file_name)
      )
    )
  );
}
<?php

declare(strict_types=1);

namespace GPDCore\Functions;


/**
 * reemplaza los acentos por las letras correspondientes sin acento
 * @param string $text
 * @return string 
 */
function removeTilde(string $text): string
{
    $text = str_replace(
        array("á", "é", "í", "ó", "ú", "Á", "É", "Í", "Ó", "Ú", "ä", "ë", "ï", "ö", "ü", "Ä", "Ë", "Ï", "Ö", "Ü", "ñ", "Ñ"),
        array("a", "e", "i", "o", "u", "A", "E", "I", "O", "U", "a", "e", "i", "o", "u", "A", "E", "I", "O", "U", "n", "N"),
        $text
    );
    return $text;
}
/**
 * reemplaza los caracteres especiales por $separator
 * @param string $text
 * @param string $separator carácter que reemplazará los caracteres especiales
 * @return string 
 */
function removeSpecialChars(string $text, string $separator = '-'): string
{
    $text = removeTilde($text);
    $text = preg_replace('/\W+/', $separator, $text);
    $text = strtolower(trim($text, $separator));

    return $text;
}

function createRandomString(int $max_chars=6): string {
    $clave="";
    $chars = array();
    for ($i="a"; $i<"z"; $i++) $chars[] = $i; // creamos vector de letras
    $chars[] = "z";
    for ($i=0; $i<$max_chars; $i++) {
        $letra = round(rand(0, 1)); // primero escogemos entre letra y número
        if ($letra) // es letra
            $clave .= $chars[round(rand(0, count($chars)-1))];
        else // es numero
            $clave .= round(rand(0, 9));
    }
    return $clave;
}
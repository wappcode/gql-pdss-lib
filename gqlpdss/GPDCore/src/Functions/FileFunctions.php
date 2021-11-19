<?php

declare(strict_types = 1);

namespace GPDCore\Functions;

/**
 * Recupera la extensión de un archivo
 * @return string
 */
function getFileExtension(string $fileName): ?string {
    $pos =strripos($fileName, ".");
    $extension=($pos===false) ? '':  substr($fileName, $pos+1);
    return $extension;
}

/**
 * Recupera solo el nombre del archivo ejemplo dirname1/dirname2/file.ext => file.ext
 * @return string
 */
function getFileNameOnly(string $src): string {
    $pos =strripos($src, DIRECTORY_SEPARATOR);
    $name=($pos===false) ? $src:  substr($src, $pos+1);
    return $name;
}
/**
 * Recupera solo el nombre del archivo ejemplo dirname1/dirname2/file.ext => file
 * @return string
 */
function getFileNameOnlyWithoutExtension(string $src): string {
    $pos =strripos($src, DIRECTORY_SEPARATOR);
    $name=($pos===false) ? $src:  substr($src, $pos+1);
    $extension = getFileExtension($name);
    $finalName = str_replace(".{$extension}", "", $name);
    return $finalName;
}

function standardizeFileName($fileName): string {
    
    $onlyName = getFileNameOnlyWithoutExtension($fileName);
    $extension = getFileExtension($fileName);
    $standardizeName  = removeTilde($onlyName);
    $standardizeName = removeSpecialChars($standardizeName);
    $resultName = $standardizeName.".".$extension;
    return strtolower($resultName);
}


// Impure functions

/**
 * Recupera el mimeType de un archivo
 * @param $src la ruta completa del archivo
 * @return string
 */
function getFileType(string $src): string {
    $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
    $type = finfo_file($fileInfo, $src);
    finfo_close($fileInfo);
    return $type;
}


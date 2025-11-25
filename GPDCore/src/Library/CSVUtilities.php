<?php

declare(strict_types=1);

namespace GPDCore\Library;

class CSVUtilities
{
    /**
     * Da formato valido de csv al valor.
     */
    public static function formatValue(string $value): string
    {
        $scaped = str_replace('"', '""', $value);

        return '"' . $scaped . '"';
    }

    /**
     * Crea una linea o fila con los valores del array.
     */
    public static function createLine(array $row): string
    {
        $values = array_map(function ($value) {
            return CSVUtilities::formatValue(($value));
        }, $row);
        $line = implode(',', $values) . "\n";

        return $line;
    }
}

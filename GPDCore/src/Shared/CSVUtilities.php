<?php

declare(strict_types=1);

namespace GPDCore\Shared;

class CSVUtilities
{
    public static function formatValue(string $value): string
    {
        $scaped = str_replace('"', '""', $value);

        return '"' . $scaped . '"';
    }

    public static function createLine(array $row): string
    {
        $values = array_map(fn($v) => self::formatValue((string)$v), $row);
        return implode(',', $values) . "\n";
    }
}

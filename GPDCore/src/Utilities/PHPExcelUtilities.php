<?php

declare(strict_types=1);

namespace GPDCore\Utilities;

use DateTime;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PHPExcelUtilities
{
    /**
     * @param $filename string  No incluir extensión
     */
    public static function setHeaders(string $filename): void
    {
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header(sprintf('Content-Disposition: attachment;filename="%s.xlsx"', $filename));
        header('Cache-Control: max-age=0');
    }

    /**
     * Establece el valor y formato de la celda como DateTime.
     *
     * @param [string, DateTime] $value
     */
    public static function setDateTimeCellValue(Worksheet $sheet, string $cell, $value): void
    {
        $date = static::getDateFromValue($value);
        if (!isset($date) || !($date instanceof DateTime)) {
            return;
        } else {
            $formatedDate = Date::PHPToExcel($date);
            $sheet->setCellValue($cell, $formatedDate);
            $sheet->getStyle($cell)
                    ->getNumberFormat()
                    ->setFormatCode(
                        NumberFormat::FORMAT_DATE_DATETIME
                    );
        }
    }

    /**
     * Establece el valor y formato de la celda como Date.
     *
     * @param [string, DateTime] $value
     */
    public static function setDateCellValue(Worksheet $sheet, string $cell, $value): void
    {
        $date = static::getDateFromValue($value);
        if (!isset($date) || !($date instanceof DateTime)) {
            return;
        } else {
            $formatedDate = Date::PHPToExcel($date);
            $sheet->setCellValue($cell, $formatedDate);
            $sheet->getStyle($cell)
                    ->getNumberFormat()
                    ->setFormatCode(
                        NumberFormat::FORMAT_DATE_DDMMYYYY
                    );
        }
    }

    protected static function getDateFromValue($value): ?DateTime
    {
        $date = null;
        if ($value instanceof DateTime) {
            $date = clone $value;
        }
        if (is_string($value) && !empty($value)) {
            $date = new DateTime($value);
        }

        return $date;
    }
}

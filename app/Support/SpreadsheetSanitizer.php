<?php

namespace App\Support;

class SpreadsheetSanitizer
{
    /**
     * Neutralize spreadsheet formula injection: a cell beginning with
     * =, +, -, @ (or tab/CR) executes as a formula when the CSV/XLSX is
     * opened in Excel or LibreOffice. Prefixing a single quote forces
     * the value to be read as text without altering how it displays.
     */
    public static function sanitize(mixed $value): mixed
    {
        if (! is_string($value) || $value === '') {
            return $value;
        }

        if (in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'".$value;
        }

        return $value;
    }

    /** Sanitize every cell in a spreadsheet row. */
    public static function row(array $cells): array
    {
        return array_map([self::class, 'sanitize'], $cells);
    }
}

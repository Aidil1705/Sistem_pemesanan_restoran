<?php

declare(strict_types=1);

namespace App;

/**
 * Shared presentation formatting helpers.
 */
class Formatter
{
    /**
     * Formats integer currency values as Indonesian rupiah.
     */
    public static function rupiah(int|float $value): string
    {
        return 'Rp' . number_format((int) $value, 0, ',', '.');
    }
}

<?php

namespace App\Support;

class NumberFormat
{
    /**
     * Format a rupiah amount into a short scale string such as "1,375.77 B".
     */
    public static function compact(float $value, string $suffixSeparator = ' '): string
    {
        $absolute = abs($value);

        [$divider, $suffix] = match (true) {
            $absolute >= 1_000_000_000 => [1_000_000_000, 'B'],
            $absolute >= 1_000_000 => [1_000_000, 'M'],
            $absolute >= 1_000 => [1_000, 'K'],
            default => [1, ''],
        };

        return number_format($value / $divider, 2).($suffix === '' ? '' : $suffixSeparator.$suffix);
    }
}

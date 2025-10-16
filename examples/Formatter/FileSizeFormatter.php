<?php

namespace App\Formatters;

use CleaniqueCoders\Placeholdify\Contracts\FormatterInterface;

/**
 * Custom formatter for file sizes
 */
class FileSizeFormatter implements FormatterInterface
{
    public function getName(): string
    {
        return 'filesize';
    }

    public function canFormat(mixed $value): bool
    {
        return is_numeric($value);
    }

    public function format(mixed $value, mixed ...$options): string
    {
        if (! is_numeric($value) || $value < 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $precision = (int) ($options[0] ?? 2);
        $bytes = (float) $value;

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision).' '.$units[$i];
    }
}

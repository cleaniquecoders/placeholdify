<?php

namespace App\Formatters;

use CleaniqueCoders\Placeholdify\Contracts\FormatterInterface;

/**
 * Custom formatter for Malaysian IC (Identity Card) numbers
 */
class MalaysianICFormatter implements FormatterInterface
{
    public function getName(): string
    {
        return 'ic';
    }

    public function canFormat(mixed $value): bool
    {
        return is_string($value) || is_numeric($value);
    }

    public function format(mixed $value, mixed ...$options): string
    {
        if (empty($value)) {
            return 'N/A';
        }

        $ic = preg_replace('/[^0-9]/', '', (string) $value);

        if (strlen($ic) !== 12) {
            return $value; // Return original if not valid length
        }

        $format = $options[0] ?? 'default';

        return match ($format) {
            'masked' => substr($ic, 0, 6).'-**-****',
            'dashed' => substr($ic, 0, 6).'-'.substr($ic, 6, 2).'-'.substr($ic, 8, 4),
            default => $ic,
        };
    }
}

<?php

namespace CleaniqueCoders\Placeholdify\Formatters;

use CleaniqueCoders\Placeholdify\Contracts\FormatterInterface;

class NumberFormatter implements FormatterInterface
{
    public function format(mixed $value, mixed ...$args): string
    {
        $decimals = $args[0] ?? 0;

        return number_format((float) $value, $decimals);
    }

    public function getName(): string
    {
        return 'number';
    }

    public function canFormat(mixed $value): bool
    {
        return is_numeric($value);
    }
}

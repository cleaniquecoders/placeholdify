<?php

namespace CleaniqueCoders\Placeholdify\Formatters;

use CleaniqueCoders\Placeholdify\Contracts\FormatterInterface;

class PercentageFormatter implements FormatterInterface
{
    public function format(mixed $value, mixed ...$args): string
    {
        $decimals = $args[0] ?? 2;

        return number_format((float) $value * 100, $decimals).'%';
    }

    public function getName(): string
    {
        return 'percentage';
    }

    public function canFormat(mixed $value): bool
    {
        return is_numeric($value);
    }
}

<?php

namespace CleaniqueCoders\Placeholdify\Formatters;

use CleaniqueCoders\Placeholdify\Contracts\FormatterInterface;

class CurrencyFormatter implements FormatterInterface
{
    public function format(mixed $value, mixed ...$args): string
    {
        $currency = $args[0] ?? 'USD';
        $decimals = $args[1] ?? 2;

        return $currency.' '.number_format((float) $value, $decimals);
    }

    public function getName(): string
    {
        return 'currency';
    }

    public function canFormat(mixed $value): bool
    {
        return is_numeric($value);
    }
}

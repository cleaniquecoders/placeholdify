<?php

namespace CleaniqueCoders\Placeholdify\Formatters;

use CleaniqueCoders\Placeholdify\Contracts\FormatterInterface;

class UpperFormatter implements FormatterInterface
{
    public function format(mixed $value, mixed ...$args): string
    {
        return strtoupper((string) $value);
    }

    public function getName(): string
    {
        return 'upper';
    }

    public function canFormat(mixed $value): bool
    {
        return is_string($value) || is_numeric($value) || (is_object($value) && method_exists($value, '__toString'));
    }
}

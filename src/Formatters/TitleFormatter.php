<?php

namespace CleaniqueCoders\Placeholdify\Formatters;

use CleaniqueCoders\Placeholdify\Contracts\FormatterInterface;
use Illuminate\Support\Str;

class TitleFormatter implements FormatterInterface
{
    public function format(mixed $value, mixed ...$args): string
    {
        return Str::title((string) $value);
    }

    public function getName(): string
    {
        return 'title';
    }

    public function canFormat(mixed $value): bool
    {
        return is_string($value) || is_numeric($value) || (is_object($value) && method_exists($value, '__toString'));
    }
}

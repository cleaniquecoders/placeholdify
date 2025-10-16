<?php

namespace CleaniqueCoders\Placeholdify\Formatters;

use CleaniqueCoders\Placeholdify\Contracts\FormatterInterface;

class SlugFormatter implements FormatterInterface
{
    public function format(mixed $value, mixed ...$args): string
    {
        $separator = $args[0] ?? '-';

        // Convert to string and create slug
        $slug = strtolower(trim((string) $value));
        $slug = preg_replace('/[^a-z0-9]+/', $separator, $slug);
        $slug = trim($slug, $separator);

        return $slug;
    }

    public function getName(): string
    {
        return 'slug';
    }

    public function canFormat(mixed $value): bool
    {
        return is_string($value) || is_numeric($value) || (is_object($value) && method_exists($value, '__toString'));
    }
}

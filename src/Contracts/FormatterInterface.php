<?php

namespace CleaniqueCoders\Placeholdify\Contracts;

interface FormatterInterface
{
    /**
     * Format the given value with optional arguments
     *
     * @param  mixed  $value  The value to format
     * @param  mixed  ...$args  Additional arguments for formatting
     * @return string The formatted value
     */
    public function format(mixed $value, mixed ...$args): string;

    /**
     * Get the formatter name/identifier
     */
    public function getName(): string;

    /**
     * Validate if the value can be formatted by this formatter
     *
     * @param  mixed  $value  The value to validate
     */
    public function canFormat(mixed $value): bool;
}

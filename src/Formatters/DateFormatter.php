<?php

namespace CleaniqueCoders\Placeholdify\Formatters;

use CleaniqueCoders\Placeholdify\Contracts\FormatterInterface;
use Illuminate\Support\Carbon;

class DateFormatter implements FormatterInterface
{
    public function format(mixed $value, mixed ...$args): string
    {
        $format = $args[0] ?? 'Y-m-d';

        if ($value instanceof Carbon) {
            return $value->format($format);
        }

        return Carbon::parse($value)->format($format);
    }

    public function getName(): string
    {
        return 'date';
    }

    public function canFormat(mixed $value): bool
    {
        if ($value instanceof Carbon) {
            return true;
        }

        try {
            Carbon::parse($value);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}

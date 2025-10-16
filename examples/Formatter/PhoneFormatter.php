<?php

namespace App\Formatters;

use CleaniqueCoders\Placeholdify\Contracts\FormatterInterface;

/**
 * Custom formatter for phone numbers
 */
class PhoneFormatter implements FormatterInterface
{
    public function getName(): string
    {
        return 'phone';
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

        $country = $options[0] ?? 'MY';
        $cleaned = preg_replace('/[^0-9]/', '', $value);

        return match ($country) {
            'MY' => $this->formatMalaysian($cleaned),
            'US' => $this->formatUS($cleaned),
            default => $cleaned,
        };
    }

    private function formatMalaysian(string $number): string
    {
        if (strlen($number) === 10 && str_starts_with($number, '01')) {
            return preg_replace('/(\d{2})(\d{4})(\d{4})/', '$1-$2 $3', $number);
        }

        if (strlen($number) === 11 && str_starts_with($number, '601')) {
            return '+6'.preg_replace('/(\d)(\d{2})(\d{4})(\d{4})/', '$1 $2-$3 $4', $number);
        }

        return $number;
    }

    private function formatUS(string $number): string
    {
        if (strlen($number) === 10) {
            return preg_replace('/(\d{3})(\d{3})(\d{4})/', '($1) $2-$3', $number);
        }

        return $number;
    }
}

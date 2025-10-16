<?php

namespace App\Services\Letters;

use CleaniqueCoders\Placeholdify\PlaceholderHandler;
use CleaniqueCoders\Placeholdify\PlaceholdifyBase;

/**
 * Property Rental Agreement Letter Generator
 */
class RentalAgreementLetter extends PlaceholdifyBase
{
    protected function configure(): void
    {
        $this->handler->setFallback('Not specified');
        $this->handler->setDelimiter('[', ']'); // Use different delimiters for rental documents

        // Register tenant context
        $this->handler->registerContext('tenant', [
            'name' => 'full_name',
            'ic' => 'identity_number',
            'phone' => 'phone_number',
            'email' => 'email',
            'occupation' => 'occupation',
        ]);

        // Register landlord context
        $this->handler->registerContext('landlord', [
            'name' => 'full_name',
            'ic' => 'identity_number',
            'phone' => 'phone_number',
            'address' => 'address',
        ]);

        // Register property context
        $this->handler->registerContext('property', [
            'address' => 'full_address',
            'type' => 'property_type',
            'size' => 'size_sqft',
            'rooms' => 'bedroom_count',
            'bathrooms' => 'bathroom_count',
        ]);

        // Register currency formatter
        $this->handler->registerFormatter('currency', function ($value, $currency = 'RM') {
            return $currency.' '.number_format($value, 2);
        });
    }

    public function build($rentalData): PlaceholderHandler
    {
        return $this->handler
            ->useContext('tenant', $rentalData->tenant, 'tenant')
            ->useContext('landlord', $rentalData->landlord, 'landlord')
            ->useContext('property', $rentalData->property, 'property')
            ->addFormatted('monthly_rent', $rentalData->monthly_rent, 'currency')
            ->addFormatted('security_deposit', $rentalData->security_deposit, 'currency')
            ->addFormatted('utility_deposit', $rentalData->utility_deposit, 'currency')
            ->addDate('lease_start', $rentalData->lease_start_date, 'F j, Y')
            ->addDate('lease_end', $rentalData->lease_end_date, 'F j, Y')
            ->addDate('agreement_date', now(), 'F j, Y')
            ->add('lease_duration', $this->calculateLeaseDuration($rentalData))
            ->add('rent_due_date', $rentalData->rent_due_day)
            ->add('agreement_reference', $this->generateAgreementReference($rentalData))
            ->add('included_utilities', $this->formatUtilities($rentalData->included_utilities))
            ->add('house_rules', $this->formatHouseRules($rentalData->house_rules))
            ->add('furnishing_details', $this->formatFurnishing($rentalData->furnishing))
            ->addIf($rentalData->pets_allowed, 'pet_clause',
                'Pets are allowed with prior written consent and additional deposit.',
                'No pets are allowed on the premises.')
            ->addIf($rentalData->smoking_allowed, 'smoking_clause',
                'Smoking is permitted in designated areas only.',
                'This is a non-smoking property.');
    }

    private function calculateLeaseDuration($rentalData): string
    {
        $start = new \DateTime($rentalData->lease_start_date);
        $end = new \DateTime($rentalData->lease_end_date);
        $interval = $start->diff($end);

        if ($interval->y > 0) {
            return $interval->y.' year(s) and '.$interval->m.' month(s)';
        }

        return $interval->m.' month(s)';
    }

    private function formatUtilities($utilities): string
    {
        if (empty($utilities)) {
            return 'No utilities included';
        }

        return collect($utilities)->map(function ($utility) {
            return "• {$utility}";
        })->join("\n");
    }

    private function formatHouseRules($rules): string
    {
        if (empty($rules)) {
            return 'Standard house rules apply';
        }

        return collect($rules)->map(function ($rule, $index) {
            return ($index + 1).". {$rule}";
        })->join("\n");
    }

    private function formatFurnishing($furnishing): string
    {
        if (empty($furnishing)) {
            return 'Unfurnished property';
        }

        $furnished = collect($furnishing)->groupBy('room');

        return $furnished->map(function ($items, $room) {
            $itemList = $items->pluck('item')->join(', ');

            return ucfirst($room).": {$itemList}";
        })->join("\n");
    }

    private function generateAgreementReference($rentalData): string
    {
        return 'RA-'.now()->year.'-'.
               substr(md5($rentalData->property->address), 0, 6).'-'.
               str_pad($rentalData->id, 4, '0', STR_PAD_LEFT);
    }
}

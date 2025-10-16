<?php

namespace App\Services\Letters;

use CleaniqueCoders\Placeholdify\PlaceholderHandler;
use CleaniqueCoders\Placeholdify\PlaceholdifyBase;

/**
 * Employment Offer Letter Generator
 */
class OfferLetter extends PlaceholdifyBase
{
    protected function configure(): void
    {
        $this->handler->setFallback('To be determined');

        // Register employee context
        $this->handler->registerContext('employee', [
            'name' => 'full_name',
            'email' => 'email',
            'phone' => 'phone_number',
            'address' => 'address',
        ]);

        // Register position context
        $this->handler->registerContext('position', [
            'title' => 'title',
            'department' => 'department.name',
            'level' => 'level',
        ]);

        // Register salary formatter
        $this->handler->registerFormatter('salary', function ($value, $currency = 'RM') {
            return $currency.' '.number_format($value, 2);
        });
    }

    public function build($offerData): PlaceholderHandler
    {
        return $this->handler
            ->useContext('employee', $offerData->candidate, 'employee')
            ->useContext('position', $offerData->position, 'position')
            ->addFormatted('salary', $offerData->annual_salary, 'salary')
            ->addFormatted('monthly_salary', $offerData->annual_salary / 12, 'salary')
            ->addDate('offer_date', now(), 'F j, Y')
            ->addDate('start_date', $offerData->start_date, 'F j, Y')
            ->addDate('response_deadline', now()->addDays(7), 'F j, Y')
            ->add('benefits', $this->formatBenefits($offerData->benefits))
            ->add('offer_reference', $this->generateOfferReference($offerData))
            ->addIf($offerData->probation_period, 'probation_text',
                "This offer is subject to a {$offerData->probation_period}-month probation period.",
                'No probation period applies.');
    }

    private function formatBenefits($benefits): string
    {
        if (empty($benefits)) {
            return 'Standard company benefits package';
        }

        return collect($benefits)->map(function ($benefit) {
            return "• {$benefit['name']}: {$benefit['description']}";
        })->join("\n");
    }

    private function generateOfferReference($offerData): string
    {
        return 'OFFER-'.now()->year.'-'.strtoupper(substr($offerData->position->title, 0, 3)).'-'.
               str_pad($offerData->id, 4, '0', STR_PAD_LEFT);
    }
}

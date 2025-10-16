<?php

namespace App\Services\Letters;

use CleaniqueCoders\Placeholdify\BaseLetter;
use CleaniqueCoders\Placeholdify\PlaceholderHandler;

/**
 * Example letter class for permit generation
 */
class PermitLetter extends BaseLetter
{
    protected function configure(): void
    {
        $this->handler->setFallback('N/A');
    }

    public function build($formAppliance): PlaceholderHandler
    {
        return $this->handler
            ->add('permitNo', $this->generatePermitNo($formAppliance))
            ->addDate('issued_at', now())
            ->addDate('expires_at', now()->addYear())
            ->useContext('student', $formAppliance->student, 'student')
            ->useContext('appliance', $formAppliance, 'appliance');
    }

    protected function generatePermitNo($formAppliance): string
    {
        return 'PERMIT-'.now()->year.'-'.$formAppliance->id;
    }
}

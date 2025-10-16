<?php

namespace App\Services\Letters;

use CleaniqueCoders\Placeholdify\PlaceholderHandler;
use CleaniqueCoders\Placeholdify\PlaceholdifyBase;

/**
 * Medical Leave Approval Letter Generator
 */
class MedicalLeaveLetter extends PlaceholdifyBase
{
    protected function configure(): void
    {
        $this->handler->setFallback('N/A');

        // Register employee context
        $this->handler->registerContext('employee', [
            'name' => 'full_name',
            'employee_id' => 'employee_id',
            'department' => 'department.name',
            'position' => 'position.title',
            'manager' => 'manager.name',
        ]);

        // Register medical context with custom formatters
        $this->handler->registerContext('medical', [
            'condition' => ['property' => 'medical_condition', 'formatter' => 'title'],
            'doctor' => 'attending_physician',
            'hospital' => 'medical_facility',
        ]);
    }

    public function build($leaveRequest): PlaceholderHandler
    {
        return $this->handler
            ->useContext('employee', $leaveRequest->employee, 'employee')
            ->useContext('medical', $leaveRequest, 'medical')
            ->addDate('leave_start', $leaveRequest->start_date, 'F j, Y')
            ->addDate('leave_end', $leaveRequest->end_date, 'F j, Y')
            ->addDate('expected_return', $leaveRequest->expected_return_date, 'F j, Y')
            ->addDate('application_date', $leaveRequest->created_at, 'F j, Y')
            ->addDate('approval_date', now(), 'F j, Y')
            ->add('leave_duration', $this->calculateLeaveDuration($leaveRequest))
            ->add('leave_reference', $this->generateLeaveReference($leaveRequest))
            ->add('leave_type', $this->determineLeaveType($leaveRequest))
            ->add('compensation_details', $this->getCompensationDetails($leaveRequest))
            ->add('return_conditions', $this->getReturnConditions($leaveRequest))
            ->add('approved_by', $leaveRequest->approved_by->name)
            ->add('hr_contact', $leaveRequest->hr_representative->name)
            ->addIf($leaveRequest->requires_medical_clearance, 'medical_clearance_text',
                'Medical clearance is required before returning to work.',
                'No additional medical clearance required.')
            ->addLazy('benefits_continuation', function () use ($leaveRequest) {
                return $this->calculateBenefitsContinuation($leaveRequest);
            });
    }

    private function calculateLeaveDuration($leaveRequest): string
    {
        $start = new \DateTime($leaveRequest->start_date);
        $end = new \DateTime($leaveRequest->end_date);
        $interval = $start->diff($end);

        $days = $interval->days + 1; // Include both start and end dates

        if ($days == 1) {
            return '1 day';
        } elseif ($days < 7) {
            return $days.' days';
        } elseif ($days < 30) {
            $weeks = floor($days / 7);
            $remainingDays = $days % 7;
            $result = $weeks.' week'.($weeks > 1 ? 's' : '');
            if ($remainingDays > 0) {
                $result .= ' and '.$remainingDays.' day'.($remainingDays > 1 ? 's' : '');
            }

            return $result;
        } else {
            $months = floor($days / 30);
            $remainingDays = $days % 30;
            $result = $months.' month'.($months > 1 ? 's' : '');
            if ($remainingDays > 0) {
                $result .= ' and '.$remainingDays.' day'.($remainingDays > 1 ? 's' : '');
            }

            return $result;
        }
    }

    private function determineLeaveType($leaveRequest): string
    {
        $duration = (new \DateTime($leaveRequest->start_date))->diff(new \DateTime($leaveRequest->end_date))->days + 1;

        if ($duration <= 3) {
            return 'Short-term Medical Leave';
        } elseif ($duration <= 30) {
            return 'Extended Medical Leave';
        } else {
            return 'Long-term Medical Leave';
        }
    }

    private function getCompensationDetails($leaveRequest): string
    {
        if ($leaveRequest->is_paid_leave) {
            return 'This leave will be compensated at 100% of your regular salary.';
        } elseif ($leaveRequest->partial_pay_percentage > 0) {
            return "This leave will be compensated at {$leaveRequest->partial_pay_percentage}% of your regular salary.";
        } else {
            return 'This is unpaid leave. Your benefits may continue as per company policy.';
        }
    }

    private function getReturnConditions($leaveRequest): string
    {
        $conditions = [];

        if ($leaveRequest->requires_medical_clearance) {
            $conditions[] = 'Submit medical clearance from attending physician';
        }

        if ($leaveRequest->requires_fitness_assessment) {
            $conditions[] = 'Complete occupational health fitness assessment';
        }

        if ($leaveRequest->requires_gradual_return) {
            $conditions[] = 'Participate in gradual return-to-work program';
        }

        if (empty($conditions)) {
            return 'No special conditions for return to work.';
        }

        return collect($conditions)->map(function ($condition, $index) {
            return ($index + 1).". {$condition}";
        })->join("\n");
    }

    private function calculateBenefitsContinuation($leaveRequest): string
    {
        $benefits = [];

        if ($leaveRequest->health_insurance_continues) {
            $benefits[] = 'Health insurance coverage will continue';
        }

        if ($leaveRequest->dental_insurance_continues) {
            $benefits[] = 'Dental insurance coverage will continue';
        }

        if ($leaveRequest->life_insurance_continues) {
            $benefits[] = 'Life insurance coverage will continue';
        }

        if (empty($benefits)) {
            return 'Please contact HR for details about benefits continuation.';
        }

        return implode(', ', $benefits).' during your leave period.';
    }

    private function generateLeaveReference($leaveRequest): string
    {
        return 'ML-'.now()->year.'-'.
               $leaveRequest->employee->employee_id.'-'.
               str_pad($leaveRequest->id, 4, '0', STR_PAD_LEFT);
    }
}

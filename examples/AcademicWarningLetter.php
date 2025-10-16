<?php

namespace App\Services\Letters;

use CleaniqueCoders\Placeholdify\BaseLetter;
use CleaniqueCoders\Placeholdify\PlaceholderHandler;

/**
 * Student Academic Warning Letter Generator
 */
class AcademicWarningLetter extends BaseLetter
{
    protected function configure(): void
    {
        $this->handler->setFallback('N/A');

        // Register student context
        $this->handler->registerContext('student', [
            'name' => 'full_name',
            'matric' => 'matric_number',
            'program' => 'program.name',
            'year' => 'current_year',
            'semester' => 'current_semester',
            'advisor' => 'academic_advisor.name',
        ]);

        // Register GPA formatter
        $this->handler->registerFormatter('gpa', function ($value) {
            return number_format($value, 2);
        });
    }

    public function build($academicRecord): PlaceholderHandler
    {
        $warningLevel = $this->determineWarningLevel($academicRecord->cgpa);

        return $this->handler
            ->useContext('student', $academicRecord->student, 'student')
            ->addFormatted('current_cgpa', $academicRecord->cgpa, 'gpa')
            ->addFormatted('required_cgpa', 2.00, 'gpa')
            ->add('warning_level', $warningLevel['level'])
            ->add('warning_description', $warningLevel['description'])
            ->add('action_required', $warningLevel['action'])
            ->addDate('semester_start', $academicRecord->semester_start, 'F j, Y')
            ->addDate('semester_end', $academicRecord->semester_end, 'F j, Y')
            ->addDate('letter_date', now(), 'F j, Y')
            ->add('letter_reference', $this->generateReference($academicRecord))
            ->add('failed_subjects', $this->formatFailedSubjects($academicRecord->failed_subjects))
            ->addLazy('improvement_plan', function () use ($academicRecord) {
                return $this->generateImprovementPlan($academicRecord);
            })
            ->addIf($academicRecord->is_final_warning, 'final_warning_text',
                'This is your FINAL WARNING. Failure to improve may result in academic dismissal.',
                'You have the opportunity to improve your academic standing.');
    }

    private function determineWarningLevel($cgpa): array
    {
        if ($cgpa >= 1.75 && $cgpa < 2.00) {
            return [
                'level' => 'First Warning',
                'description' => 'Your CGPA has fallen below the minimum requirement',
                'action' => 'Meet with academic advisor and develop improvement plan',
            ];
        } elseif ($cgpa >= 1.50 && $cgpa < 1.75) {
            return [
                'level' => 'Second Warning',
                'description' => 'Your academic performance continues to be unsatisfactory',
                'action' => 'Mandatory counseling and reduced course load',
            ];
        } else {
            return [
                'level' => 'Final Warning',
                'description' => 'Your CGPA is critically low',
                'action' => 'Immediate intervention required or face dismissal',
            ];
        }
    }

    private function formatFailedSubjects($subjects): string
    {
        if (empty($subjects)) {
            return 'No failed subjects this semester';
        }

        return collect($subjects)->map(function ($subject) {
            return "• {$subject->code} - {$subject->name} (Grade: {$subject->grade})";
        })->join("\n");
    }

    private function generateImprovementPlan($academicRecord): string
    {
        $plan = [
            '1. Meet with academic advisor within 7 days',
            '2. Attend mandatory study skills workshop',
            '3. Utilize tutoring services for weak subjects',
        ];

        if ($academicRecord->cgpa < 1.75) {
            $plan[] = '4. Reduce course load to 12 credit hours maximum';
            $plan[] = '5. Submit weekly progress reports';
        }

        return implode("\n", $plan);
    }

    private function generateReference($academicRecord): string
    {
        return 'AW-'.now()->year.'-'.$academicRecord->student->matric_number.'-'.
               str_pad($academicRecord->semester, 2, '0', STR_PAD_LEFT);
    }
}

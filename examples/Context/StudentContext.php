<?php

namespace App\Services\Placeholders;

use CleaniqueCoders\Placeholdify\PlaceholderHandler;

/**
 * Example context class for student-related placeholders
 */
class StudentContext
{
    public static function build($student): PlaceholderHandler
    {
        $handler = new PlaceholderHandler;

        return $handler
            ->add('name', $student->student_name)
            ->add('matric', $student->matric_number)
            ->add('email', $student->email)
            ->add('program', $student->program->name ?? 'Unknown Program')
            ->addDate('enrollment_date', $student->enrolled_at, 'd/m/Y')
            ->addFormatted('gpa', $student->gpa, 'number', 2)
            ->addIf('honors', $student->gpa >= 3.5, 'Dean\'s List', '')
            ->addLazy('transcript_url', function () use ($student) {
                return route('student.transcript', $student->id);
            });
    }

    /**
     * Register context mapping for student objects
     */
    public static function registerMapping(PlaceholderHandler $handler): void
    {
        $handler->registerContextMapping('student', [
            'name' => 'student_name',
            'matric' => 'matric_number',
            'email' => 'email',
            'program' => 'program.name',
            'faculty' => 'program.faculty.name',
            'semester' => 'current_semester',
            'year' => 'academic_year',
            'status' => fn ($student) => ucfirst($student->status ?? 'active'),
            'full_program' => fn ($student) => "{$student->program->name} - {$student->program->faculty->name}",
        ]);
    }
}

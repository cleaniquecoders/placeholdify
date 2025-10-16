<?php

/**
 * Simple usage examples for Placeholdify
 */

require_once __DIR__.'/../vendor/autoload.php';

use CleaniqueCoders\Placeholdify\PlaceholderHandler;
use CleaniqueCoders\Placeholdify\Placeholdify;

// Example 1: Basic usage
echo "=== Example 1: Basic Usage ===\n";
$template = 'Hello {name}, your order #{orderNo} totaling {amount} has been confirmed.';

$content = PlaceholderHandler::process($template, [
    'name' => 'John Doe',
    'orderNo' => '12345',
    'amount' => '$99.99',
]);

echo $content."\n\n";

// Example 2: Fluent API
echo "=== Example 2: Fluent API ===\n";
$handler = new PlaceholderHandler;

$user = (object) [
    'name' => 'Jane Smith',
    'phone' => null,
    'mobile' => '123-456-7890',
];

$content = $handler
    ->add('name', $user->name)
    ->addDate('today', new DateTime, 'F j, Y')
    ->addNullable('phone', $user->phone, $user->mobile)
    ->replace('Dear {name}, today is {today}. Contact: {phone}');

echo $content."\n\n";

// Example 3: Custom Formatters
echo "=== Example 3: Custom Formatters ===\n";
$handler = new PlaceholderHandler;

// Register custom formatter
$handler->registerFormatter('currency', function ($value, $currency = 'USD') {
    return $currency.' '.number_format($value, 2);
});

$content = $handler
    ->addFormatted('total', 1234.56, 'currency', 'MYR')
    ->addFormatted('name', 'john doe', 'upper')
    ->replace('Customer: {name}, Total: {total}');

echo $content."\n\n";

// Example 4: Template Modifiers
echo "=== Example 4: Template Modifiers ===\n";
$template = 'Student: {name|upper}, Amount: {fee|currency:MYR}, Date: {created_at|date:d/m/Y}';

$handler = new PlaceholderHandler;
$content = $handler
    ->add('name', 'john doe')
    ->add('fee', 150.50)
    ->add('created_at', new DateTime('2024-01-15'))
    ->replaceWithModifiers($template);

echo $content."\n\n";

// Example 5: Context Registration
echo "=== Example 5: Context Registration ===\n";
$student = (object) [
    'student_name' => 'Alice Johnson',
    'email' => 'alice@university.edu',
    'matric_number' => 'A12345',
    'program' => (object) ['name' => 'Computer Science'],
];

$handler = new PlaceholderHandler;

// Register context once
$handler->registerContext('student', [
    'name' => 'student_name',
    'email' => 'email',
    'matric' => 'matric_number',
    'program' => 'program.name', // Nested relationships
]);

// Use anywhere
$content = $handler
    ->useContext('student', $student, 'student')
    ->replace("Student: {student.name} ({student.matric})\nEmail: {student.email}\nProgram: {student.program}");

echo $content."\n\n";

// Example 6: Lazy Evaluation
echo "=== Example 6: Lazy Evaluation ===\n";
$handler = new PlaceholderHandler;

$expensiveData = null;
$content = $handler
    ->add('simple', 'Simple value')
    ->addLazy('expensive', function () use (&$expensiveData) {
        echo "Computing expensive operation...\n";
        $expensiveData = 'Computed result';

        return $expensiveData;
    })
    ->replace('Simple: {simple}, Expensive: {expensive}');

echo $content."\n\n";

echo "All examples completed!\n";

# Context System

The context system allows you to register reusable mappings for your models, making it easy to extract and map data consistently across your application.

## Basic Context Usage

### Registering a Context

```php
$handler->registerContext('student', [
    'name' => 'student_name',
    'email' => 'email',
    'matric' => 'matric_number',
    'program' => 'program.name', // Nested relationships
]);
```

### Using a Context

```php
$handler->useContext('student', $student, 'student');

// Access as: {student.name}, {student.email}, {student.matric}
```

## Advanced Context Mapping

### Using Closures

```php
$handler->registerContext('user', [
    'name' => 'name',
    'email' => 'email',
    'role' => fn($user) => $user->roles->pluck('name')->join(', '),
    'full_name' => fn($user) => $user->first_name . ' ' . $user->last_name,
]);
```

### Nested Object Access

```php
$handler->registerContext('order', [
    'number' => 'order_number',
    'total' => 'total_amount',
    'customer_name' => 'customer.name',
    'customer_email' => 'customer.email',
    'shipping_address' => 'shipping.address.full_address',
]);
```

### With Formatters

```php
$handler->registerContext('invoice', [
    'number' => 'invoice_number',
    'total' => ['property' => 'total', 'formatter' => 'currency', 'args' => ['MYR']],
    'date' => ['property' => 'created_at', 'formatter' => 'date', 'args' => ['d/m/Y']],
]);
```

## Global Contexts

Register contexts globally in your service provider for reuse across the application:

```php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use CleaniqueCoders\Placeholdify\PlaceholderHandler;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        PlaceholderHandler::registerGlobalContext('user', [
            'name' => 'name',
            'email' => 'email',
            'role' => fn($user) => $user->roles->pluck('name')->join(', '),
            'avatar' => fn($user) => $user->getFirstMediaUrl('avatar'),
        ]);

        PlaceholderHandler::registerGlobalContext('company', [
            'name' => 'name',
            'address' => 'address',
            'phone' => 'phone',
            'email' => 'contact_email',
            'logo' => fn($company) => $company->getFirstMediaUrl('logo'),
        ]);
    }
}
```

## Context from Objects

Extract data from objects with custom mappings:

```php
$handler->addFromContext('student', $student, [
    'name' => 'student_name',
    'email' => 'email',
    'formatted_name' => ['property' => 'student_name', 'formatter' => 'upper'],
    'full_details' => fn($s) => $s->name . ' (' . $s->matric . ')',
    'gpa' => ['property' => 'gpa', 'formatter' => 'number', 'args' => [2]],
]);
```

## Configuration Context

Define contexts in your configuration file:

```php
// config/placeholdify.php
return [
    'contexts' => [
        'user' => [
            'name' => 'name',
            'email' => 'email',
            'created' => ['property' => 'created_at', 'formatter' => 'date', 'args' => ['F j, Y']],
        ],
        'product' => [
            'name' => 'name',
            'price' => ['property' => 'price', 'formatter' => 'currency', 'args' => ['USD']],
            'category' => 'category.name',
        ],
    ],
];
```

## Real-World Examples

### Student Context

```php
PlaceholderHandler::registerGlobalContext('student', [
    'name' => 'student_name',
    'matric' => 'matric_number',
    'email' => 'email',
    'phone' => 'phone_number',
    'program' => 'program.name',
    'faculty' => 'program.faculty.name',
    'semester' => 'current_semester',
    'gpa' => ['property' => 'gpa', 'formatter' => 'number', 'args' => [2]],
    'status' => fn($student) => $student->is_active ? 'Active' : 'Inactive',
    'full_name' => fn($student) => $student->student_name . ' (' . $student->matric_number . ')',
]);
```

### Invoice Context

```php
PlaceholderHandler::registerGlobalContext('invoice', [
    'number' => 'invoice_number',
    'subtotal' => ['property' => 'subtotal', 'formatter' => 'currency', 'args' => ['MYR']],
    'tax' => ['property' => 'tax_amount', 'formatter' => 'currency', 'args' => ['MYR']],
    'total' => ['property' => 'total', 'formatter' => 'currency', 'args' => ['MYR']],
    'date' => ['property' => 'created_at', 'formatter' => 'date', 'args' => ['d/m/Y']],
    'due_date' => ['property' => 'due_date', 'formatter' => 'date', 'args' => ['d/m/Y']],
    'status' => 'status',
    'customer_name' => 'customer.name',
    'customer_email' => 'customer.email',
    'items_count' => fn($invoice) => $invoice->items()->count(),
]);
```

### Company Context

```php
PlaceholderHandler::registerGlobalContext('company', [
    'name' => 'name',
    'registration' => 'registration_number',
    'address' => fn($company) => implode(', ', array_filter([
        $company->street,
        $company->city,
        $company->state,
        $company->postal_code,
    ])),
    'phone' => 'phone',
    'email' => 'email',
    'website' => 'website',
    'established' => ['property' => 'established_at', 'formatter' => 'date', 'args' => ['Y']],
]);
```

## Usage in Templates

```php
// Using multiple contexts
$handler = new PlaceholderHandler();
$content = $handler
    ->useContext('student', $student, 'student')
    ->useContext('company', $company, 'company')
    ->useContext('invoice', $invoice, 'invoice')
    ->replace($template);
```

Template example:

```text
INVOICE #{invoice.number}

From:
{company.name}
{company.address}
Phone: {company.phone}
Email: {company.email}

To:
{student.name} ({student.matric})
{student.email}
Program: {student.program}

Invoice Details:
Date: {invoice.date}
Due Date: {invoice.due_date}
Subtotal: {invoice.subtotal}
Tax: {invoice.tax}
Total: {invoice.total}

Status: {invoice.status}
```

## Context Validation

Validate that required properties exist before using a context:

```php
$handler->validateContext('student', $student, ['name', 'email', 'matric']);
```

## Dynamic Context Loading

Load contexts dynamically based on conditions:

```php
$contextName = $user->role === 'student' ? 'student' : 'staff';
$handler->useContext($contextName, $user, 'user');
```

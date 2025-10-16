# Placeholdify

[![Latest Version on Packagist](https://img.shields.io/packagist/v/cleaniquecoders/placeholdify.svg?style=flat-square)](https://packagist.org/packages/cleaniquecoders/placeholdify)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/cleaniquecoders/placeholdify/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/cleaniquecoders/placeholdify/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/cleaniquecoders/placeholdify/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/cleaniquecoders/placeholdify/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/cleaniquecoders/placeholdify.svg?style=flat-square)](https://packagist.org/packages/cleaniquecoders/placeholdify)
[![License](https://img.shields.io/packagist/l/cleaniquecoders/placeholdify.svg?style=flat-square)](https://packagist.org/packages/cleaniquecoders/placeholdify)

A powerful and extendable placeholder replacement engine for Laravel that makes working with dynamic templates a breeze. Perfect for generating letters, invoices, certificates, emails, and any document that requires dynamic content injection.

## Why Placeholdify?

- 🎯 **Context-Aware** - Register reusable context mappings for your models
- 🎨 **Custom Formatters** - Built-in formatters for dates, currency, numbers, and easy custom formatter registration
- ⚡ **Lazy Evaluation** - Defer expensive operations until needed
- 🔧 **Template Modifiers** - Support inline formatting like `{amount|currency:USD}` or `{name|upper}`
- 🧩 **Extendable** - Easily create dedicated context classes for different document types
- 💬 **Fluent API** - Chainable methods for clean, readable code
- 🛡️ **Safe Defaults** - Built-in fallback values and error handling
- 📦 **Zero Dependencies** - Works with plain Laravel, no extra packages required

## Installation

You can install the package via composer:

```bash
composer require cleaniquecoders/placeholdify
```

Optionally, publish the config file:

```bash
php artisan vendor:publish --tag=placeholdify-config
```

## Quick Start

### Basic Usage

```php
use CleaniqueCoders\Placeholdify\PlaceholderHandler;

$template = "Hello {name}, your order #{orderNo} totaling {amount} has been confirmed.";

$content = PlaceholderHandler::process($template, [
    'name' => 'John Doe',
    'orderNo' => '12345',
    'amount' => '$99.99'
]);

// Output: "Hello John Doe, your order #12345 totaling $99.99 has been confirmed."
```

### Fluent API

```php
$handler = new PlaceholderHandler();

$content = $handler
    ->add('name', $user->name)
    ->addDate('today', now())
    ->addNullable('phone', $user->phone, $user->mobile)
    ->replace($template);
```

## Features

### 1. Date Formatting

Automatically format dates using Carbon:

```php
$handler->addDate('invoice_date', $invoice->created_at, 'd/m/Y');
$handler->addDate('due_date', $invoice->due_date, 'F j, Y');
```

### 2. Null Coalescing

Provide fallback values in a clean chain:

```php
$handler->addNullable('contact', $user->email, $user->phone, 'N/A');
```

### 3. Custom Formatters

Register and use custom formatters:

```php
// Register formatter
$handler->registerFormatter('currency', function($value, $currency = 'USD') {
    return $currency . ' ' . number_format($value, 2);
});

// Use formatter
$handler->addFormatted('total', 1234.56, 'currency', 'MYR');
// Result: {total} becomes "MYR 1,234.56"
```

**Built-in Formatters:**

- `date` - Format dates
- `currency` - Format currency
- `number` - Format numbers
- `upper` - Convert to uppercase
- `lower` - Convert to lowercase
- `title` - Convert to title case

### 4. Template Modifiers

Use inline modifiers directly in your templates:

```php
$template = "Student: {name|upper}, Amount: {fee|currency:MYR}, Date: {created_at|date:d/m/Y}";

$handler = new PlaceholderHandler();
$content = $handler
    ->add('name', 'john doe')
    ->add('fee', 150.50)
    ->add('created_at', now())
    ->replaceWithModifiers($template);

// Output: "Student: JOHN DOE, Amount: MYR 150.50, Date: 16/10/2025"
```

### 5. Context Registration

Register reusable context mappings for your models:

```php
// Register once (e.g., in a service provider)
$handler->registerContext('student', [
    'name' => 'student_name',
    'email' => 'email',
    'matric' => 'matric_number',
    'program' => 'program.name', // Nested relationships
]);

// Use anywhere
$handler->useContext('student', $student, 'student');

// Access as: {student.name}, {student.email}, {student.matric}
```

### 6. Lazy Evaluation

Defer expensive operations:

```php
$handler->addLazy('total_items', function() use ($order) {
    return $order->items()->sum('amount');
});
```

### 7. Conditional Placeholders

Add placeholders based on conditions:

```php
$handler->addIf(
    $student->gpa >= 3.5,
    'honors',
    'with Honors',
    ''
);
```

### 8. Context from Objects

Automatically extract data from objects:

```php
$handler->addFromContext('student', $student, [
    'name' => 'student_name',
    'email' => 'email',
    'formatted_name' => ['property' => 'student_name', 'formatter' => 'upper'],
    'full_details' => fn($s) => $s->name . ' (' . $s->matric . ')',
]);
```

## Advanced Usage

### Creating Dedicated Context Classes

For complex scenarios, create dedicated context classes:

```php
namespace App\Services\Placeholders;

use CleaniqueCoders\Placeholdify\PlaceholderHandler;

class InvoiceContext
{
    public static function build($invoice): PlaceholderHandler
    {
        $handler = new PlaceholderHandler();

        return $handler
            ->add('invoice_no', $invoice->invoice_number)
            ->addFormatted('total', $invoice->total, 'currency', 'MYR')
            ->addFormatted('subtotal', $invoice->subtotal, 'currency', 'MYR')
            ->addDate('invoice_date', $invoice->created_at, 'd/m/Y')
            ->addDate('due_date', $invoice->due_date, 'd/m/Y')
            ->useContext('customer', $invoice->customer, 'customer')
            ->addLazy('items_list', function() use ($invoice) {
                return $invoice->items
                    ->map(fn($i) => $i->description . ' - RM' . number_format($i->amount, 2))
                    ->join("\n");
            });
    }
}

// Usage
$content = InvoiceContext::build($invoice)->replace($template);
```

### Creating Template Classes

Extend the base class for different document types:

```php
namespace App\Services\Templates;

use CleaniqueCoders\Placeholdify\PlaceholdifyBase;

class PermitTemplate extends PlaceholdifyBase
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
        return 'PERMIT-' . now()->year . '-' . $formAppliance->id;
    }
}

// Usage
$template = new PermitTemplate();
$content = $template->generate($formAppliance, $templateContent);
```

### Registering Global Contexts

Register common contexts in your service provider:

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
        ]);

        PlaceholderHandler::registerGlobalContext('company', [
            'name' => 'name',
            'address' => 'address',
            'phone' => 'phone',
            'email' => 'contact_email',
        ]);
    }
}
```

## Real-World Example

Generate a student permit letter:

```php
use CleaniqueCoders\Placeholdify\PlaceholderHandler;

class PermitController extends Controller
{
    public function generate($id)
    {
        $application = ApplianceApplication::with(['student', 'appliance'])->findOrFail($id);
        $template = LetterTemplate::where('type', 'permit')->first();

        $handler = new PlaceholderHandler();
        $content = $handler
            ->add('permitNo', 'MIMET/2024/C01/KTL003/' . $application->id)
            ->addDate('created_at', $application->created_at, 'd M Y')
            ->addDate('expires_at', now()->addYear(), 'd M Y')
            ->addNullable('studentName', $application->student->student_name)
            ->addNullable('matricNo', $application->student->matric_number)
            ->addNullable('applianceType', $application->appliance->type)
            ->addNullable('applianceName', $application->appliance->name)
            ->addNullable('serialNo', $application->serial_number)
            ->addNullable('approvedBy', $application->approvedBy->name)
            ->replace($template->content);

        return view('letters.preview', compact('content'));
    }
}
```

**Template Example:**

```text
APPLIANCE PERMIT

Permit No: {permitNo}
Issue Date: {created_at}
Expiry Date: {expires_at}

This is to certify that {studentName} (Matric: {matricNo}) is permitted to use:

Appliance Type: {applianceType}
Appliance Name: {applianceName}
Serial Number: {serialNo}

Approved by: {approvedBy}
```

## Configuration

Customize default behavior in `config/placeholdify.php`:

```php
return [
    'delimiter' => [
        'start' => '{',
        'end' => '}',
    ],
    'fallback' => 'N/A',
    'formatters' => [
        // Register global formatters
    ],
    'contexts' => [
        // Register global contexts
    ],
];
```

## API Reference

### PlaceholderHandler Methods

| Method | Description |
|--------|-------------|
| `add($key, $value, $fallback = null)` | Add a single placeholder |
| `addMany(array $placeholders)` | Add multiple placeholders |
| `addDate($key, $date, $format, $fallback = null)` | Add formatted date |
| `addNullable($key, ...$values)` | Add with null coalescing |
| `addFormatted($key, $value, $formatter, ...$args)` | Add with custom formatter |
| `addLazy($key, Closure $callback, $fallback = null)` | Add lazy-evaluated placeholder |
| `addIf($condition, $key, $value, $elseValue = null)` | Add conditional placeholder |
| `addFromContext($prefix, $object, array $mapping)` | Add from object context |
| `useContext($name, $object, $prefix = '')` | Use registered context |
| `registerContext($name, array $mapping)` | Register reusable context |
| `registerFormatter($name, Closure $formatter)` | Register custom formatter |
| `setDelimiter($start, $end = null)` | Set custom delimiters |
| `setFallback($value)` | Set default fallback value |
| `replace($content)` | Replace placeholders in content |
| `replaceWithModifiers($content)` | Replace with modifier support |
| `all()` | Get all placeholders |
| `clear()` | Clear all placeholders |

### Static Methods

| Method | Description |
|--------|-------------|
| `PlaceholderHandler::process($content, $placeholders, $delimiter = '{}')` | Quick one-liner replacement |

## Artisan Command

Placeholdify includes an Artisan command for testing and demonstration:

```bash
# Show demo with examples
php artisan placeholdify:demo

# Process custom template
php artisan placeholdify:demo --template="Hello {name}!" --data='{"name":"World"}'
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Nasrul Hazim Bin Mohamad](https://github.com/nasrulhazim)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

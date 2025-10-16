# GitHub Copilot Instructions for Placeholdify

## Project Overview

Placeholdify is a powerful Laravel package for dynamic template placeholder replacement. It provides a fluent API for managing placeholders with advanced features like context mapping, custom formatters, lazy evaluation, and template modifiers.

## Core Concepts

### 1. PlaceholderHandler
The main class that handles all placeholder operations:

```php
use CleaniqueCoders\Placeholdify\PlaceholderHandler;

$handler = new PlaceholderHandler();
$content = $handler
    ->add('name', $user->name)
    ->addDate('date', now(), 'd/m/Y')
    ->replace($template);
```

### 2. Context Mapping
Register reusable mappings for models:

```php
$handler->registerContext('student', [
    'name' => 'student_name',
    'email' => 'email',
    'program' => 'program.name', // supports dot notation
]);
```

### 3. Formatters
Built-in and custom formatters for data transformation:

```php
$handler->addFormatted('amount', 1234.56, 'currency', 'MYR');
$handler->registerFormatter('custom', function($value, ...$args) {
    return strtoupper($value);
});
```

## Code Patterns

### When creating placeholder handlers:
- Use fluent API for chaining methods
- Prefer `addNullable()` for optional values with fallbacks
- Use `addLazy()` for expensive operations
- Use `addFromContext()` for object property extraction

### When working with dates:
```php
$handler->addDate('created_at', $model->created_at, 'd/m/Y');
$handler->addDate('expires_at', now()->addYear(), 'F j, Y');
```

### When working with contexts:
```php
// Register once (in service provider)
$handler->registerContext('user', [
    'name' => 'name',
    'email' => 'email',
    'role' => fn($user) => $user->roles->pluck('name')->join(', '),
]);

// Use anywhere
$handler->useContext('user', $user, 'user');
```

### When creating dedicated context classes:
```php
namespace App\Services\Placeholders;

use CleaniqueCoders\Placeholdify\PlaceholderHandler;

class InvoiceContext
{
    public static function build($invoice): PlaceholderHandler
    {
        return (new PlaceholderHandler())
            ->add('invoice_no', $invoice->number)
            ->addFormatted('total', $invoice->total, 'currency', 'MYR')
            ->addDate('date', $invoice->created_at, 'd/m/Y')
            ->useContext('customer', $invoice->customer, 'customer');
    }
}
```

## File Structure Guidelines

### Source Files Location: `src/`
- `PlaceholderHandler.php` - Main handler class
- `BaseLetter.php` - Base class for letter generation
- `Contracts/` - Interfaces
- `Formatters/` - Custom formatter classes
- `Contexts/` - Context mapping classes

### Configuration: `config/placeholdify.php`
```php
return [
    'delimiter' => [
        'start' => '{',
        'end' => '}',
    ],
    'fallback' => 'N/A',
    'formatters' => [
        // Global formatters
    ],
    'contexts' => [
        // Global contexts
    ],
];
```

### Tests Location: `tests/`
- `PlaceholderHandlerTest.php`
- `FormatterTest.php`
- `ContextTest.php`

## Naming Conventions

### Methods:
- `add()` - Basic placeholder addition
- `addDate()` - Date-specific addition
- `addNullable()` - Null-safe addition
- `addFormatted()` - With formatter
- `addLazy()` - Lazy evaluation
- `addIf()` - Conditional addition
- `addFromContext()` - Object property extraction

### Classes:
- `*Context` - Context mapping classes
- `*Letter` - Letter generation classes
- `*Formatter` - Custom formatter classes

## Error Handling

### Always provide fallback values:
```php
$handler->add('optional_field', $value, 'N/A');
$handler->setFallback('Default Value');
```

### Handle missing context gracefully:
```php
$handler->addNullable('contact', $user->email, $user->phone, 'Contact not available');
```

## Testing Patterns

### Test placeholder replacement:
```php
public function test_basic_placeholder_replacement()
{
    $handler = new PlaceholderHandler();
    $result = $handler
        ->add('name', 'John')
        ->replace('Hello {name}');

    $this->assertEquals('Hello John', $result);
}
```

### Test formatters:
```php
public function test_date_formatter()
{
    $handler = new PlaceholderHandler();
    $result = $handler
        ->addDate('date', '2024-01-15', 'd/m/Y')
        ->replace('Date: {date}');

    $this->assertEquals('Date: 15/01/2024', $result);
}
```

## Common Use Cases

### Letter Generation:
```php
class PermitLetter extends BaseLetter
{
    public function build($application): PlaceholderHandler
    {
        return $this->handler
            ->add('permit_no', $this->generatePermitNo($application))
            ->addDate('issued_at', now())
            ->useContext('student', $application->student, 'student')
            ->useContext('appliance', $application->appliance, 'appliance');
    }
}
```

### Invoice/Receipt Generation:
```php
$handler = new PlaceholderHandler();
$content = $handler
    ->add('invoice_no', $invoice->number)
    ->addFormatted('subtotal', $invoice->subtotal, 'currency', 'MYR')
    ->addFormatted('tax', $invoice->tax, 'currency', 'MYR')
    ->addFormatted('total', $invoice->total, 'currency', 'MYR')
    ->addDate('date', $invoice->created_at, 'd/m/Y')
    ->replace($template);
```

### Email Templates:
```php
$handler = new PlaceholderHandler();
$content = $handler
    ->add('name', $user->name)
    ->add('subject', $notification->subject)
    ->addDate('date', now(), 'F j, Y')
    ->replace($emailTemplate);
```

## Best Practices

1. **Use context mapping** for reusable model mappings
2. **Prefer fluent API** for readable code
3. **Use appropriate add methods** based on data type
4. **Always provide fallbacks** for optional data
5. **Create dedicated classes** for complex scenarios
6. **Register global contexts** in service providers
7. **Use lazy evaluation** for expensive operations
8. **Test all placeholder scenarios** thoroughly

## Migration from Other Systems

When replacing existing template systems:
1. Identify existing placeholder patterns
2. Map to Placeholdify methods
3. Create context mappings for models
4. Register custom formatters if needed
5. Update templates to use new delimiter format

## Performance Considerations

- Use `addLazy()` for expensive database queries
- Register contexts once, use multiple times
- Cache formatted results when possible
- Use appropriate data types for formatters

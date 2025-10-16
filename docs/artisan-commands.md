# Artisan Commands

Placeholdify provides Artisan commands to help you create templates, contexts, and formatters quickly.

## Component Creation Command

The `make:placeholder` command generates new component classes (templates, contexts, or formatters) with proper structure and boilerplate code.

### Usage

```bash
php artisan make:placeholder {name} {type} {--list}
```

### Parameters

- `name`: The name of your component class (required)
- `type`: Component type - `template`, `context`, or `formatter` (required)
- `--list`: List all available component types and usage examples

### Available Component Types

#### Templates

Creates template classes that extend `PlaceholdifyBase` for handling content placeholders.

#### Contexts

Creates context classes that implement `ContextInterface` for reusable object mappings.

#### Formatters

Creates formatter classes that implement `FormatterInterface` for custom data transformation.

### Default Directory Structure

All components are created under the configured base path (default: `app/Services/Placeholders`):

- **Templates**: `app/Services/Placeholders/Templates/`
- **Contexts**: `app/Services/Placeholders/Contexts/`
- **Formatters**: `app/Services/Placeholders/Formatters/`

### Examples

#### List Available Component Types

```bash
php artisan make:placeholder --list
```

#### Create a Template

```bash
php artisan make:placeholder InvoiceTemplate template
```

This creates `app/Services/Placeholders/Templates/InvoiceTemplate.php`:

```php
<?php

namespace App\Services\Placeholders\Templates;

use CleaniqueCoders\Placeholdify\PlaceholderHandler;
use CleaniqueCoders\Placeholdify\PlaceholdifyBase;

/**
 * InvoiceTemplate Template
 *
 * Template for handling placeholders in content.
 */
class InvoiceTemplate extends PlaceholdifyBase
{
    protected function configure(): void
    {
        $this->handler->setFallback('N/A');

        // Add any custom configuration here
        // Example: $this->handler->registerFormatter('custom', function($value) { return strtoupper($value); });
    }

    public function build(mixed $data): PlaceholderHandler
    {
        return $this->handler
            ->add('title', $data->title ?? 'Untitled')
            ->addDate('created_at', now(), 'F j, Y')
            ->add('content', $data->content ?? '');
            // Add more placeholders as needed
    }
}
```

#### Create a Context

```bash
php artisan make:placeholder UserContext context
```

This creates `app/Services/Placeholders/Contexts/UserContext.php`:

```php
<?php

namespace App\Services\Placeholders\Contexts;

use CleaniqueCoders\Placeholdify\Contracts\ContextInterface;

/**
 * UserContext Context
 *
 * Context class for user object mappings.
 */
class UserContext implements ContextInterface
{
    public function getName(): string
    {
        return 'user';
    }

    public function getMapping(): array
    {
        return [
            'id' => 'id',
            'name' => 'name',
            'created_at' => fn($object) => $object->created_at?->format('F j, Y') ?? 'Unknown',
            // Add more mappings as needed
        ];
    }

    public function canProcess(mixed $object): bool
    {
        // Implement your validation logic here
        return is_object($object) && property_exists($object, 'id');
    }

    public function getSupportedTypes(): array
    {
        return [
            // Add supported class names or interfaces
            // Example: User::class, 'App\\Models\\User'
        ];
    }
}
```

#### Create a Formatter

```bash
php artisan make:placeholder PhoneFormatter formatter
```

This creates `app/Services/Placeholders/Formatters/PhoneFormatter.php`:

```php
<?php

namespace App\Services\Placeholders\Formatters;

use CleaniqueCoders\Placeholdify\Contracts\FormatterInterface;

/**
 * PhoneFormatter Formatter
 *
 * Custom formatter for phone data transformation.
 */
class PhoneFormatter implements FormatterInterface
{
    public function getName(): string
    {
        return 'phone';
    }

    public function canFormat(mixed $value): bool
    {
        // Implement your validation logic here
        return is_string($value) || is_numeric($value);
    }

    public function format(mixed $value, mixed ...$options): string
    {
        if (empty($value)) {
            return 'N/A';
        }

        // Implement your formatting logic here
        // You can use $options for additional parameters

        return (string) $value;
    }
}
```

## Configuration

### Base Path Configuration

The base path for component creation can be configured in `config/placeholdify.php`:

```php
return [
    'template_path' => 'app/Services/Placeholders',
    // other configuration...
];
```

### Directory Structure

The command creates components in organized subdirectories:

```text
app/Services/Placeholders/
├── Templates/
│   ├── InvoiceTemplate.php
│   └── NewsletterTemplate.php
├── Contexts/
│   ├── UserContext.php
│   └── CustomerContext.php
└── Formatters/
    ├── PhoneFormatter.php
    └── CurrencyFormatter.php
```

# Basic Usage

## Quick Start

The simplest way to use Placeholdify is with the static `process` method:

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

## Fluent API

For more control, use the fluent API:

```php
$handler = new PlaceholderHandler();

$content = $handler
    ->add('name', $user->name)
    ->addDate('today', now())
    ->addNullable('phone', $user->phone, $user->mobile)
    ->replace($template);
```

## Adding Placeholders

### Basic Addition

```php
$handler->add('name', 'John Doe');
$handler->add('email', $user->email, 'N/A'); // with fallback
```

### Multiple Placeholders

```php
$handler->addMany([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'phone' => '+1234567890',
]);
```

### Null Coalescing

Provide multiple fallback values:

```php
$handler->addNullable('contact', $user->email, $user->phone, 'No contact available');
```

## Date Formatting

Format dates using Carbon:

```php
$handler->addDate('invoice_date', $invoice->created_at, 'd/m/Y');
$handler->addDate('due_date', $invoice->due_date, 'F j, Y');
```

## Replacement

```php
$content = $handler->replace($template);
```

## Custom Delimiters

Change the default `{}` delimiters:

```php
$handler->setDelimiter('{{', '}}');
// or
$handler->setDelimiter('[[]]'); // Both start and end
```

## Fallback Values

Set a default fallback for missing placeholders:

```php
$handler->setFallback('N/A');
```

## Clearing Placeholders

Remove all placeholders:

```php
$handler->clear();
```

## Getting All Placeholders

Retrieve all registered placeholders:

```php
$placeholders = $handler->all();
```

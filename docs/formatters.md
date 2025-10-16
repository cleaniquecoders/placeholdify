# Formatters

Placeholdify provides a powerful formatter system for transforming placeholder values before replacement. All formatters must implement the `FormatterInterface` contract for consistency and type safety.

## Configuration

### Built-in Formatters Configuration

You can control which built-in formatters are automatically registered in your `config/placeholdify.php`:

```php
'built_in_formatters' => [
    'date' => true,      // Enable date formatter
    'currency' => true,  // Enable currency formatter
    'number' => true,    // Enable number formatter
    'upper' => false,    // Disable upper formatter
    'lower' => true,     // Enable lower formatter
    'title' => true,     // Enable title formatter
],
```

### Custom Formatter Classes

Register custom formatter classes in the config:

```php
'formatters' => [
    'slug' => \App\Formatters\SlugFormatter::class,
    'phone' => \App\Formatters\PhoneFormatter::class,
],
```## Using Built-in Formatters

### Basic Usage

```php
$handler->addFormatted('total', 1234.56, 'currency', 'MYR');
// Result: {total} becomes "MYR 1,234.56"
```

### Available Built-in Formatters

#### Date Formatter

```php
$handler->addFormatted('created_at', now(), 'date', 'd/m/Y');
$handler->addFormatted('published_at', $post->created_at, 'date', 'F j, Y');
```

#### Currency Formatter

```php
$handler->addFormatted('price', 99.99, 'currency', 'USD');
// Output: "USD 99.99"

$handler->addFormatted('total', 1234.56, 'currency', 'MYR', 2);
// Output: "MYR 1,234.56"
```

#### Number Formatter

```php
$handler->addFormatted('count', 1234, 'number', 0);
// Output: "1,234"

$handler->addFormatted('percentage', 0.157, 'number', 2);
// Output: "0.16"
```

#### Text Formatters

```php
$handler->addFormatted('name', 'john doe', 'upper');
// Output: "JOHN DOE"

$handler->addFormatted('email', 'USER@EXAMPLE.COM', 'lower');
// Output: "user@example.com"

$handler->addFormatted('title', 'hello world', 'title');
// Output: "Hello World"
```

## Creating Custom Formatters

### Formatter Classes (Recommended)

Create a formatter class that implements `FormatterInterface`:

```php
<?php

namespace App\Formatters;

use CleaniqueCoders\Placeholdify\Contracts\FormatterInterface;

class SlugFormatter implements FormatterInterface
{
    public function format(mixed $value, mixed ...$args): string
    {
        $separator = $args[0] ?? '-';

        $slug = strtolower(trim((string) $value));
        $slug = preg_replace('/[^a-z0-9]+/', $separator, $slug);
        $slug = trim($slug, $separator);

        return $slug;
    }

    public function getName(): string
    {
        return 'slug';
    }

    public function canFormat(mixed $value): bool
    {
        return is_string($value) || is_numeric($value) ||
               (is_object($value) && method_exists($value, '__toString'));
    }
}
```

### Registering Formatter Classes

#### Via Config (Recommended)

Add to `config/placeholdify.php`:

```php
'formatters' => [
    'slug' => \App\Formatters\SlugFormatter::class,
    'phone' => \App\Formatters\PhoneFormatter::class,
],
```

#### Manually

```php
$handler->registerFormatter(new \App\Formatters\SlugFormatter());
```

### Using Custom Formatters

```php
$handler->addFormatted('title', 'Hello World Test!', 'slug');
// Output: "hello-world-test"

$handler->addFormatted('title', 'Hello World Test!', 'slug', '_');
// Output: "hello_world_test"

$handler->addFormatted('phone', '1234567890', 'phone', 'US');
// Output: "(123) 456-7890"
```

## Formatter Management

### Check Available Formatters

```php
// Check if formatter exists
if ($handler->hasFormatter('slug')) {
    // Use the formatter
}

// Get all registered formatters
$formatters = $handler->getRegisteredFormatters();
// Returns: ['date', 'currency', 'upper', 'slug', ...]
```

### Remove Formatters

```php
// Remove a specific formatter
$handler->unregisterFormatter('upper');
```

## Global Formatters

Register formatters globally in your service provider:

```php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use CleaniqueCoders\Placeholdify\PlaceholderHandler;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Register formatter classes globally
        PlaceholderHandler::registerGlobalFormatter('slug', \App\Formatters\SlugFormatter::class);
        PlaceholderHandler::registerGlobalFormatter('phone', \App\Formatters\PhoneFormatter::class);
    }
}
```

## Template Modifiers

Use formatters directly in templates with the modifier syntax:

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

### Chaining Modifiers

```php
$template = "Message: {content|truncate:50|upper}";
```

## Formatter Configuration

Configure default formatters in `config/placeholdify.php`:

```php
return [
    'formatters' => [
        'money' => [
            'class' => \App\Formatters\MoneyFormatter::class,
            'method' => 'format',
        ],
        'custom' => function($value, $param) {
            return strtoupper($value . $param);
        },
    ],
];
```

## Creating Formatter Classes

For complex formatting logic, create dedicated formatter classes:

```php
namespace App\Formatters;

class AddressFormatter
{
    public function format($address, $style = 'full')
    {
        switch ($style) {
            case 'short':
                return $address->city . ', ' . $address->state;
            case 'full':
            default:
                return implode(', ', array_filter([
                    $address->street,
                    $address->city,
                    $address->state,
                    $address->postal_code,
                    $address->country,
                ]));
        }
    }
}

// Register in service provider
PlaceholderHandler::registerGlobalFormatter('address', [
    new \App\Formatters\AddressFormatter(),
    'format'
]);

// Usage
$handler->addFormatted('address', $user->address, 'address', 'short');
```

## More examples

See [Formatter Examples](./formatters-examples.md)

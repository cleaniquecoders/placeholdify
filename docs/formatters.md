# Formatters

Placeholdify provides a powerful formatter system for transforming placeholder values before replacement.

## Using Built-in Formatters

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

$handler->addFormatted('total', 1234.56, 'currency', 'MYR');
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

## Custom Formatters

### Registering Custom Formatters

```php
// Simple formatter
$handler->registerFormatter('reverse', function($value) {
    return strrev($value);
});

// Formatter with parameters
$handler->registerFormatter('truncate', function($value, $length = 50, $suffix = '...') {
    return strlen($value) > $length
        ? substr($value, 0, $length) . $suffix
        : $value;
});

// Advanced formatter
$handler->registerFormatter('mask_email', function($value) {
    $parts = explode('@', $value);
    if (count($parts) !== 2) return $value;

    $username = $parts[0];
    $domain = $parts[1];

    $maskedUsername = substr($username, 0, 2) . str_repeat('*', strlen($username) - 2);
    return $maskedUsername . '@' . $domain;
});
```

### Using Custom Formatters

```php
$handler->addFormatted('description', $longText, 'truncate', 100, '...');
$handler->addFormatted('email', 'john@example.com', 'mask_email');
// Output: "jo****@example.com"
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
        PlaceholderHandler::registerGlobalFormatter('money', function($value, $currency = 'MYR') {
            return $currency . ' ' . number_format($value, 2);
        });

        PlaceholderHandler::registerGlobalFormatter('phone', function($value) {
            // Format phone numbers
            return preg_replace('/(\d{3})(\d{3})(\d{4})/', '($1) $2-$3', $value);
        });
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

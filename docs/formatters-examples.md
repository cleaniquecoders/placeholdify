# Formatter Configuration Examples

## Example 1: Disabling Built-in Formatters

```php
// config/placeholdify.php
return [
    'built_in_formatters' => [
        'date' => true,      // Keep date formatter
        'currency' => true,  // Keep currency formatter
        'number' => false,   // Disable number formatter
        'upper' => false,    // Disable upper formatter
        'lower' => true,     // Keep lower formatter
        'title' => true,     // Keep title formatter
    ],
];
```

## Example 2: Custom Formatter Classes

```php
// app/Formatters/PhoneFormatter.php
<?php

namespace App\Formatters;

use CleaniqueCoders\Placeholdify\Contracts\FormatterInterface;

class PhoneFormatter implements FormatterInterface
{
    public function format(mixed $value, mixed ...$args): string
    {
        $country = $args[0] ?? 'US';

        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $value);

        if ($country === 'US' && strlen($phone) === 10) {
            return sprintf('(%s) %s-%s',
                substr($phone, 0, 3),
                substr($phone, 3, 3),
                substr($phone, 6)
            );
        }

        return $value; // Return original if can't format
    }

    public function getName(): string
    {
        return 'phone';
    }

    public function canFormat(mixed $value): bool
    {
        return is_string($value) || is_numeric($value);
    }
}
```

```php
// config/placeholdify.php
return [
    'formatters' => [
        'phone' => \App\Formatters\PhoneFormatter::class,
        'slug' => \App\Formatters\SlugFormatter::class,
    ],
];
```

## Example 3: Multiple Custom Formatters

```php
// config/placeholdify.php
return [
    // Control built-in formatters
    'built_in_formatters' => [
        'date' => true,
        'currency' => true,
        'number' => true,
        'upper' => false,  // Disable this
        'lower' => true,
        'title' => true,
    ],

    // Add custom formatter classes
    'formatters' => [
        'phone' => \App\Formatters\PhoneFormatter::class,
        'slug' => \App\Formatters\SlugFormatter::class,
        'percentage' => \App\Formatters\PercentageFormatter::class,
    ],
    ],
];
```## Example 4: Runtime Formatter Management

```php
$handler = new PlaceholderHandler();

// Check what formatters are available
$formatters = $handler->getRegisteredFormatters();
echo "Available formatters: " . implode(', ', $formatters);

// Add a custom formatter at runtime
$handler->registerFormatter(new \App\Formatters\CustomFormatter());

// Remove a formatter if needed
$handler->unregisterFormatter('upper');

// Check if a specific formatter exists
if ($handler->hasFormatter('phone')) {
    $handler->addFormatted('contact', '1234567890', 'phone', 'US');
}
```

## Example 5: Service Provider Registration

```php
// app/Providers/AppServiceProvider.php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use CleaniqueCoders\Placeholdify\PlaceholderHandler;
use App\Formatters\CustomFormatter;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Register formatter classes globally
        PlaceholderHandler::registerGlobalFormatter('custom', CustomFormatter::class);
        PlaceholderHandler::registerGlobalFormatter('money', \App\Formatters\MoneyFormatter::class);
    }
}
```

# Configuration

Customize Placeholdify behavior through the configuration file and service provider registration.

## Publishing Configuration

Publish the configuration file to customize default settings:

```bash
php artisan vendor:publish --tag=placeholdify-config
```

This creates `config/placeholdify.php` with the following structure:

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Placeholder Delimiters
    |--------------------------------------------------------------------------
    |
    | Define the start and end delimiters for placeholders in templates.
    | Default uses curly braces: {placeholder}
    |
    */
    'delimiter' => [
        'start' => '{',
        'end' => '}',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Fallback Value
    |--------------------------------------------------------------------------
    |
    | The default value to use when a placeholder is not found.
    | Set to null to use empty string.
    |
    */
    'fallback' => 'N/A',

    /*
    |--------------------------------------------------------------------------
    | Global Formatters
    |--------------------------------------------------------------------------
    |
    | Register formatters that will be available globally across all
    | PlaceholderHandler instances.
    |
    */
    'formatters' => [
        'money' => [
            'class' => \App\Formatters\MoneyFormatter::class,
            'method' => 'format',
        ],
        'phone' => function($value) {
            return preg_replace('/(\d{3})(\d{3})(\d{4})/', '($1) $2-$3', $value);
        },
    ],

    /*
    |--------------------------------------------------------------------------
    | Global Contexts
    |--------------------------------------------------------------------------
    |
    | Register context mappings that will be available globally across all
    | PlaceholderHandler instances.
    |
    */
    'contexts' => [
        'user' => [
            'name' => 'name',
            'email' => 'email',
            'created' => ['property' => 'created_at', 'formatter' => 'date', 'args' => ['F j, Y']],
        ],
        'company' => [
            'name' => 'name',
            'address' => 'address',
            'phone' => 'phone',
            'email' => 'contact_email',
        ],
    ],
];
```

## Configuration Options

### Delimiters

Change the placeholder delimiters:

```php
'delimiter' => [
    'start' => '{{',
    'end' => '}}',
],
```

Supported delimiter styles:

```php
// Curly braces (default)
'delimiter' => ['start' => '{', 'end' => '}']

// Double curly braces
'delimiter' => ['start' => '{{', 'end' => '}}']

// Square brackets
'delimiter' => ['start' => '[', 'end' => ']']

// Angle brackets
'delimiter' => ['start' => '<', 'end' => '>']

// Custom delimiters
'delimiter' => ['start' => '[[', 'end' => ']]']
```

### Fallback Value

Set the default value for missing placeholders:

```php
// Use "N/A" for missing placeholders
'fallback' => 'N/A',

// Use empty string
'fallback' => '',

// Use null (no replacement)
'fallback' => null,

// Use custom message
'fallback' => '[Missing Value]',
```

### Global Formatters

Register formatters that are available across all handlers:

```php
'formatters' => [
    // Using closure
    'percentage' => function($value, $decimals = 2) {
        return number_format($value * 100, $decimals) . '%';
    },

    // Using class method
    'currency' => [
        'class' => \App\Formatters\CurrencyFormatter::class,
        'method' => 'format',
    ],

    // Using invokable class
    'mask_email' => \App\Formatters\EmailMaskFormatter::class,
],
```

### Global Contexts

Register context mappings for common models:

```php
'contexts' => [
    'user' => [
        'name' => 'name',
        'email' => 'email',
        'phone' => 'phone',
        'role' => fn($user) => $user->roles->pluck('name')->join(', '),
        'avatar' => fn($user) => $user->getFirstMediaUrl('avatar'),
    ],

    'order' => [
        'number' => 'order_number',
        'total' => ['property' => 'total', 'formatter' => 'currency', 'args' => ['USD']],
        'status' => 'status',
        'customer_name' => 'customer.name',
        'items_count' => fn($order) => $order->items->count(),
    ],
],
```

## Service Provider Registration

The service provider is automatically registered when using Laravel's package auto-discovery. For manual registration, add to `config/app.php`:

```php
'providers' => [
    // Other providers...
    CleaniqueCoders\Placeholdify\PlaceholdifyServiceProvider::class,
],
```

## Custom Service Provider

Create a custom service provider to register application-specific formatters and contexts:

```php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use CleaniqueCoders\Placeholdify\PlaceholderHandler;

class PlaceholdifyServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerGlobalFormatters();
        $this->registerGlobalContexts();
    }

    protected function registerGlobalFormatters(): void
    {
        PlaceholderHandler::registerGlobalFormatter('money', function($value, $currency = 'MYR') {
            return $currency . ' ' . number_format($value, 2);
        });

        PlaceholderHandler::registerGlobalFormatter('percentage', function($value, $decimals = 2) {
            return number_format($value * 100, $decimals) . '%';
        });

        PlaceholderHandler::registerGlobalFormatter('truncate', function($value, $length = 50, $suffix = '...') {
            return strlen($value) > $length
                ? substr($value, 0, $length) . $suffix
                : $value;
        });
    }

    protected function registerGlobalContexts(): void
    {
        PlaceholderHandler::registerGlobalContext('student', [
            'name' => 'student_name',
            'matric' => 'matric_number',
            'email' => 'email',
            'program' => 'program.name',
            'faculty' => 'program.faculty.name',
            'gpa' => ['property' => 'gpa', 'formatter' => 'number', 'args' => [2]],
        ]);

        PlaceholderHandler::registerGlobalContext('company', [
            'name' => 'name',
            'registration' => 'registration_number',
            'address' => fn($company) => $this->formatAddress($company),
            'contact' => 'phone',
            'email' => 'email',
        ]);
    }

    protected function formatAddress($company): string
    {
        return implode(', ', array_filter([
            $company->street,
            $company->city,
            $company->state,
            $company->postal_code,
        ]));
    }
}
```

## Environment-Specific Configuration

Configure different settings per environment:

### Development Environment

```php
// config/placeholdify.php
'fallback' => env('PLACEHOLDIFY_FALLBACK', '[DEV_MISSING]'),

'formatters' => [
    'debug' => function($value) {
        return "[DEBUG: {$value}]";
    },
],
```

### Production Environment

```php
// In production .env
PLACEHOLDIFY_FALLBACK=""

// Disable debug formatters in production
'formatters' => array_filter([
    'currency' => \App\Formatters\CurrencyFormatter::class,
    'date' => \App\Formatters\DateFormatter::class,
    'debug' => app()->environment('local') ? function($value) {
        return "[DEBUG: {$value}]";
    } : null,
]),
```

## Runtime Configuration

Override configuration at runtime:

```php
// Override delimiters
$handler = new PlaceholderHandler();
$handler->setDelimiter('[[', ']]');

// Override fallback
$handler->setFallback('Missing Data');

// Add environment-specific formatters
if (app()->environment('local')) {
    $handler->registerFormatter('debug', function($value) {
        return "[DEBUG: {$value}]";
    });
}
```

## Validation Configuration

Add validation for configuration values:

```php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class PlaceholdifyConfigServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->validateConfiguration();
    }

    protected function validateConfiguration(): void
    {
        $config = config('placeholdify');

        $validator = Validator::make($config, [
            'delimiter.start' => 'required|string|min:1',
            'delimiter.end' => 'required|string|min:1',
            'fallback' => 'nullable|string',
            'formatters' => 'array',
            'contexts' => 'array',
        ]);

        if ($validator->fails()) {
            throw new \InvalidArgumentException(
                'Invalid Placeholdify configuration: ' . $validator->errors()->first()
            );
        }
    }
}
```

## Performance Configuration

Optimize configuration for performance:

```php
'cache' => [
    'enable' => env('PLACEHOLDIFY_CACHE', true),
    'ttl' => env('PLACEHOLDIFY_CACHE_TTL', 3600),
    'prefix' => 'placeholdify',
],

'optimization' => [
    'precompile_templates' => env('PLACEHOLDIFY_PRECOMPILE', false),
    'lazy_load_formatters' => true,
    'cache_contexts' => true,
],
```

## Testing Configuration

Override configuration in tests:

```php
namespace Tests\Feature;

use Tests\TestCase;
use CleaniqueCoders\Placeholdify\PlaceholderHandler;

class PlaceholdifyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Override configuration for testing
        config([
            'placeholdify.fallback' => '[TEST_MISSING]',
            'placeholdify.delimiter.start' => '{{',
            'placeholdify.delimiter.end' => '}}',
        ]);
    }

    public function test_uses_test_configuration()
    {
        $handler = new PlaceholderHandler();
        $result = $handler->replace('Hello {{missing}}');

        $this->assertEquals('Hello [TEST_MISSING]', $result);
    }
}
```

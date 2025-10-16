# Placeholdify

[![Latest Version on Packagist](https://img.shields.io/packagist/v/cleaniquecoders/placeholdify.svg?style=flat-square)](https://packagist.org/packages/cleaniquecoders/placeholdify)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/cleaniquecoders/placeholdify/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/cleaniquecoders/placeholdify/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/cleaniquecoders/placeholdify/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/cleaniquecoders/placeholdify/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/cleaniquecoders/placeholdify.svg?style=flat-square)](https://packagist.org/packages/cleaniquecoders/placeholdify)
[![License](https://img.shields.io/packagist/l/cleaniquecoders/placeholdify.svg?style=flat-square)](https://packagist.org/packages/cleaniquecoders/placeholdify)

A powerful and extendable placeholder replacement engine for Laravel that makes working with dynamic templates a breeze. Perfect for generating letters, invoices, certificates, emails, and any document that requires dynamic content injection.

## Features

- 🎯 **Context-Aware** - Register reusable context mappings for your models
- 🎨 **Custom Formatters** - Built-in formatters for dates, currency, numbers, and easy custom formatter registration
- ⚡ **Lazy Evaluation** - Defer expensive operations until needed
- 🔧 **Template Modifiers** - Support inline formatting like `{amount|currency:USD}` or `{name|upper}`
- 🧩 **Extendable** - Easily create dedicated context classes for different document types
- 💬 **Fluent API** - Chainable methods for clean, readable code
- 🛡️ **Safe Defaults** - Built-in fallback values and error handling
- 📦 **Zero Dependencies** - Works with plain Laravel, no extra packages required

## Installation

```bash
composer require cleaniquecoders/placeholdify
```

Optionally, publish the config file:

```bash
php artisan vendor:publish --tag=placeholdify-config
```

## Quick Start

### Simple Replacement

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
    ->addDate('today', now(), 'F j, Y')
    ->addFormatted('total', 1234.56, 'currency', 'MYR')
    ->replace($template);
```

### Using Contexts

```php
// Register context once
$handler->registerContext('user', [
    'name' => 'name',
    'email' => 'email',
    'role' => fn($user) => $user->roles->pluck('name')->join(', '),
]);

// Use anywhere
$content = $handler
    ->useContext('user', $user, 'customer')
    ->replace('Hello {customer.name}, your role is {customer.role}');
```

### Template Modifiers

```php
$template = "Student: {name|upper}, Amount: {fee|currency:MYR}, Date: {created_at|date:d/m/Y}";

$content = $handler
    ->add('name', 'john doe')
    ->add('fee', 150.50)
    ->add('created_at', now())
    ->replaceWithModifiers($template);

// Output: "Student: JOHN DOE, Amount: MYR 150.50, Date: 16/10/2025"
```

## Template Classes

Create dedicated template classes for complex scenarios:

```php
use CleaniqueCoders\Placeholdify\PlaceholdifyBase;

class InvoiceTemplate extends PlaceholdifyBase
{
    protected function configure(): void
    {
        $this->handler->setFallback('N/A');
    }

    public function build($invoice): PlaceholderHandler
    {
        return $this->handler
            ->add('invoice_no', $invoice->invoice_number)
            ->addFormatted('total', $invoice->total, 'currency', 'MYR')
            ->addDate('invoice_date', $invoice->created_at, 'd/m/Y')
            ->useContext('customer', $invoice->customer, 'customer');
    }
}

// Usage
$template = new InvoiceTemplate();
$content = $template->generate($invoice, $templateContent);
```

## Artisan Commands

Generate new template classes:

```bash
php artisan placeholdify:make-template InvoiceTemplate --type=invoice
```

Demo the package functionality:

```bash
php artisan placeholdify:demo
```

## Documentation

For comprehensive documentation, examples, and advanced usage:

- 📖 **[Complete Documentation](docs/index.md)** - Full documentation index
- 🚀 **[Basic Usage](docs/basic-usage.md)** - Learn the fundamentals
- 🎨 **[Formatters](docs/formatters.md)** - Built-in and custom formatters
- 🔄 **[Context System](docs/context-system.md)** - Reusable model mappings
- ⚡ **[Advanced Features](docs/advanced-features.md)** - Lazy evaluation, conditionals, and more
- 🏗️ **[Template Classes](docs/template-classes.md)** - Organize your template logic
- ⚙️ **[Configuration](docs/configuration.md)** - Customize default behavior
- 🛠️ **[Artisan Commands](docs/artisan-commands.md)** - Command-line tools
- 🌍 **[Real World Examples](docs/real-world-examples.md)** - Complete implementation examples
- 📚 **[API Reference](docs/api-reference.md)** - Complete method documentation

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

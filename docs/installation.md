# Installation

## Requirements

- PHP 8.1 or higher
- Laravel 9.0 or higher

## Composer Installation

You can install the package via composer:

```bash
composer require cleaniquecoders/placeholdify
```

## Configuration

Optionally, publish the config file:

```bash
php artisan vendor:publish --tag=placeholdify-config
```

This will publish the configuration file to `config/placeholdify.php` where you can customize:

- Placeholder delimiters
- Default fallback values
- Global formatters
- Global contexts

## Publishing Stubs

If you want to customize the template stubs for the `make-template` command:

```bash
php artisan vendor:publish --tag=placeholdify-stubs
```

This publishes the stubs to `stubs/placeholdify/` where you can modify them according to your needs.

## Service Provider

The service provider is automatically registered. If you need to manually register it, add it to your `config/app.php`:

```php
'providers' => [
    // ...
    CleaniqueCoders\Placeholdify\PlaceholdifyServiceProvider::class,
],
```

# Placeholdify

[![Latest Version on Packagist](https://img.shields.io/packagist/v/cleaniquecoders/placeholdify.svg?style=flat-square)](https://packagist.org/packages/cleaniquecoders/placeholdify)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/cleaniquecoders/placeholdify/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/cleaniquecoders/placeholdify/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/cleaniquecoders/placeholdify/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/cleaniquecoders/placeholdify/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/cleaniquecoders/placeholdify.svg?style=flat-square)](https://packagist.org/packages/cleaniquecoders/placeholdify)

A powerful and extendable placeholder replacement engine for Laravel templates with context mapping, custom formatters, and fluent API

## Installation

You can install the package via composer:

```bash
composer require cleaniquecoders/placeholdify
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="placeholdify-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="placeholdify-config"
```

This is the contents of the published config file:

```php
return [
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="placeholdify-views"
```

## Usage

```php
$placeholdify = new CleaniqueCoders\Placeholdify();
echo $placeholdify->echoPhrase('Hello, CleaniqueCoders!');
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

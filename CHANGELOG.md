# Changelog

All notable changes to `placeholdify` will be documented in this file.

## 1.1.0 - 2026-03-31

### What's Changed

#### Added

- Laravel 13 support (illuminate constraints include `^13.0`)
- PHPUnit 12 compatibility
- Pest 4 support

#### Changed

- Updated `phpunit.xml.dist` for PHPUnit 12
- Standardized CI workflow (Laravel 12 + PHP 8.4/8.3)
- Updated dev dependencies (larastan, phpstan plugins, collision)

**Full Changelog**: https://github.com/cleaniquecoders/placeholdify/compare/1.0.0...1.1.0

## First Release - 2025-10-16

### Placeholdify v1.0.0 Release Notes

#### 🎉 Initial Release

We're excited to announce the first stable release of **Placeholdify** - a powerful Laravel package for dynamic template placeholder replacement.

#### ✨ Key Features

##### Core Functionality

- **Fluent API** - Chainable methods for clean, readable code
- **Static Process Method** - Quick one-liner for simple replacements
- **Custom Delimiters** - Configurable `{}`, `{{}}`, or any custom delimiters
- **Fallback Values** - Global and per-placeholder fallback support

##### Advanced Features

- **Context Mapping** - Reusable object property mappings with dot notation support
- **Built-in Formatters** - Currency, date, number, filesize, and text formatters
- **Custom Formatters** - Easy interface for creating your own formatters
- **Lazy Evaluation** - Defer expensive operations until needed
- **Template Modifiers** - Inline formatting like `{amount|currency:USD}`

##### Developer Experience

- **Artisan Commands** - Generate templates, contexts, and formatters
- **Template Classes** - Dedicated classes extending `PlaceholdifyBase`
- **Zero Dependencies** - Works with vanilla Laravel
- **Type Safety** - Full PHP 8.4+ type declarations
- **Comprehensive Tests** - Well-tested with Pest

#### 🚀 Quick Start

```bash
composer require cleaniquecoders/placeholdify


```
```php
use CleaniqueCoders\Placeholdify\PlaceholderHandler;

$content = PlaceholderHandler::process($template, [
    'name' => 'John Doe',
    'amount' => '$99.99'
]);


```
#### 📋 Artisan Commands

- `php artisan make:placeholder {name} {type}` - Generate components
- `php artisan make:placeholder --list` - List available types

#### 🎯 Perfect For

- Invoice and receipt generation
- Email templates
- Document automation
- Certificate generation
- Legal document templates
- Academic institutional letters

#### 🏗️ Built-in Formatters

- `currency` - Format monetary values
- `date` - Date formatting with Carbon
- `number` - Number formatting with precision
- `filesize` - Human-readable file sizes
- `upper/lower/title` - Text case formatting

#### 🔧 Requirements

- PHP 8.4+
- Laravel 11.0+ or 12.0+

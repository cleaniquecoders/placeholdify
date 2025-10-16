# Documentation Index

Welcome to the Placeholdify documentation. This guide will help you understand and effectively use Placeholdify for dynamic template placeholder replacement in your Laravel applications.

## Getting Started

- **[Installation](installation.md)** - Install and configure Placeholdify
- **[Basic Usage](basic-usage.md)** - Learn the fundamentals of placeholder replacement

## Core Features

- **[Formatters](formatters.md)** - Transform placeholder values with built-in and custom formatters
- **[Context System](context-system.md)** - Reusable mappings for models and objects
- **[Advanced Features](advanced-features.md)** - Lazy evaluation, conditionals, and template modifiers

## Template Management

- **[Template Classes](template-classes.md)** - Create dedicated template classes for organized code
- **[Artisan Commands](artisan-commands.md)** - Generate templates and demo functionality

## Configuration & Customization

- **[Configuration](configuration.md)** - Customize delimiters, fallbacks, and global settings

## Examples & Reference

- **[Real World Examples](real-world-examples.md)** - Complete examples for common use cases
- **[API Reference](api-reference.md)** - Complete method and class documentation

## Quick Navigation

### For Beginners

1. Start with [Installation](installation.md)
2. Learn [Basic Usage](basic-usage.md)
3. Explore [Real World Examples](real-world-examples.md)

### For Advanced Users

1. Master [Formatters](formatters.md)
2. Understand the [Context System](context-system.md)
3. Explore [Advanced Features](advanced-features.md)
4. Create [Template Classes](template-classes.md)

### For Reference

- [API Reference](api-reference.md) - Complete method documentation
- [Configuration](configuration.md) - All configuration options
- [Artisan Commands](artisan-commands.md) - Command-line tools

## Document Structure

Each documentation file follows a consistent structure:

- **Overview** - What the feature does
- **Basic Examples** - Simple usage patterns
- **Advanced Examples** - Complex scenarios
- **Configuration** - Related settings
- **Best Practices** - Recommended approaches

## Key Concepts

### Placeholders

Template variables in the format `{key}` that get replaced with actual values.

### Formatters

Functions that transform placeholder values (e.g., `{price|currency:USD}`).

### Contexts

Reusable mappings that extract data from objects consistently.

### Templates

Classes that organize placeholder logic for specific document types.

## Common Patterns

### Simple Replacement

```php
PlaceholderHandler::process($template, $data);
```

### Fluent API

```php
$handler = new PlaceholderHandler();
$content = $handler
    ->add('name', $user->name)
    ->addDate('date', now())
    ->replace($template);
```

### Template Classes

```php
class InvoiceTemplate extends PlaceholdifyBase
{
    public function build($invoice): PlaceholderHandler
    {
        return $this->handler
            ->useContext('customer', $invoice->customer, 'customer')
            ->addFormatted('total', $invoice->total, 'currency');
    }
}
```

## Support & Contributing

- **Issues**: Report bugs and request features on GitHub
- **Documentation**: Help improve these docs
- **Code**: Contribute to the codebase

## Version Information

This documentation is for Placeholdify v2.x. For older versions, please refer to the appropriate tagged documentation.

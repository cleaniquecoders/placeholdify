# Placeholdify Examples

This directory contains comprehensive examples demonstrating the features and capabilities of the Placeholdify package. The examples are organized into four main categories:

## 📁 Directory Structure

### 1. [Context](./Context/) - Context Mapping Examples

Examples showing how to create and use context mappings for reusable model configurations.

**Files:**

- `context_examples.php` - Basic context registration and usage patterns
- `InvoiceContext.php` - Invoice-specific context implementation
- `UserContext.php` - User model context with dynamic mappings
- `StudentContext.php` - Academic student context example

**Key Features Demonstrated:**

- Context registration and mapping
- Nested object property access with dot notation
- Dynamic context functions and lazy evaluation
- Multiple context usage in single templates
- Complex nested data structures

### 2. [Formatter](./Formatter/) - Custom Formatter Examples

Examples of creating and using custom formatters for specialized data transformation.

**Files:**

- `PhoneFormatter.php` - Phone number formatting (Malaysian/US formats)
- `FileSizeFormatter.php` - File size formatting (B, KB, MB, GB, etc.)
- `MalaysianICFormatter.php` - Malaysian Identity Card number formatting

**Key Features Demonstrated:**

- Creating custom FormatterInterface implementations
- Using built-in formatters (currency, date, percentage, etc.)
- Template modifier syntax (`{value|formatter:options}`)
- Chaining multiple formatters
- Format validation and error handling

### 3. [Templates](./Templates/) - Template Class Examples

Complete template class implementations extending PlaceholdifyBase for specific document types.

**Files:**

- `AcademicWarningLetter.php` - Academic warning letter template
- `EventInvitationLetter.php` - Event invitation template
- `MedicalLeaveLetter.php` - Medical leave application template
- `OfferLetter.php` - Employment offer letter template
- `RentalAgreementLetter.php` - Rental agreement template

**Key Features Demonstrated:**

- Template class inheritance from `PlaceholdifyBase`
- Complex document generation
- Multi-step template processing
- Real-world business document creation
- Context integration within templates

### 4. [Usage](./Usage/) - Real-world Usage Examples

Complete real-world implementations showing practical Laravel controller applications.

**Files:**

- `LetterGenerationController.php` - Laravel controller implementation
- `PermitController.php` - Permit application controller

**Key Features Demonstrated:**

- Laravel integration patterns
- Controller-based template processing
- HTTP request handling with placeholders
- Complex document generation workflows
- Business logic integration with templates

## 🚀 Running the Examples

### Prerequisites

```bash
# Ensure you're in the package root directory
cd /path/to/placeholdify

# Install dependencies
composer install
```

### Running Individual Examples

**Context Examples:**
```bash
php examples/Context/context_examples.php
```

**Running Template Classes:**
```bash
# Template classes are designed to be used within Laravel applications
# See the Usage examples for implementation patterns
```

**Using Controller Examples:**
```bash
# Copy the controller examples to your Laravel application
# and adapt them to your specific routes and requirements
```

### Using in Laravel Applications

For Laravel integration, copy the relevant classes to your application:

```bash
# Copy context classes to your app
cp examples/Context/UserContext.php app/Services/Placeholders/
cp examples/Context/StudentContext.php app/Services/Placeholders/
cp examples/Context/InvoiceContext.php app/Services/Placeholders/

# Copy custom formatters
cp examples/Formatter/PhoneFormatter.php app/Formatters/
cp examples/Formatter/FileSizeFormatter.php app/Formatters/
cp examples/Formatter/MalaysianICFormatter.php app/Formatters/

# Copy letter templates
cp examples/Templates/OfferLetter.php app/Services/Letters/
cp examples/Templates/AcademicWarningLetter.php app/Services/Letters/
cp examples/Templates/EventInvitationLetter.php app/Services/Letters/

# Copy controller examples for reference
cp examples/Usage/LetterGenerationController.php app/Http/Controllers/
cp examples/Usage/PermitController.php app/Http/Controllers/
```

Then register them in your service provider:

```php
// In AppServiceProvider or dedicated service provider
use App\Formatters\PhoneFormatter;
use CleaniqueCoders\Placeholdify\PlaceholderHandler;

public function boot()
{
    $handler = new PlaceholderHandler;
    $handler->registerFormatter(new PhoneFormatter);

    // Register global contexts
    $handler->registerContextMapping('user', [
        'name' => 'name',
        'email' => 'email',
        // ... other mappings
    ]);
}
```

## 📖 Learning Path

### 1. Start with Context Examples

Begin with `Context/context_examples.php` to understand:

- Basic placeholder replacement
- Fluent API usage
- Built-in formatters
- Template modifiers

### 2. Explore Context Mapping

Study the context classes in `Context/` to learn:

- Context registration
- Object property mapping
- Dynamic functions
- Multiple contexts

### 3. Create Custom Formatters

Examine the formatter classes in `Formatter/` to understand:

- FormatterInterface implementation
- Custom formatting logic
- Template modifier integration

### 4. Build Real Applications

Examine the `Templates/` classes and `Usage/` controllers for:

- Template class architecture
- Laravel integration
- Complex document generation
- Business logic integration

## 🎯 Common Patterns

### Context Registration Pattern
```php
// Register once (in service provider)
$handler->registerContextMapping('user', [
    'name' => 'name',
    'email' => 'email',
    'role' => fn($user) => ucfirst($user->role),
]);

// Use anywhere
$handler->useContext('user', $user, 'user');
```

### Template Class Pattern
```php
class MyTemplate extends PlaceholdifyBase
{
    public function build($data): PlaceholderHandler
    {
        return $this->handler
            ->add('field', $data->value)
            ->addDate('date', now())
            ->useContext('user', $data->user, 'user');
    }
}
```

### Custom Formatter Pattern
```php
class MyFormatter implements FormatterInterface
{
    public function getName(): string { return 'myformat'; }
    public function canFormat(mixed $value): bool { return true; }
    public function format(mixed $value, mixed ...$options): string
    {
        // Your formatting logic here
        return $formatted;
    }
}
```

## 📚 Additional Resources

- [Main Documentation](../docs/index.md)
- [Installation Guide](../docs/installation.md)
- [API Reference](../docs/api-reference.md)
- [Configuration Options](../docs/configuration.md)

## 🤝 Contributing

Found an issue or want to add more examples? Please see our [main README](../README.md) for contribution guidelines.

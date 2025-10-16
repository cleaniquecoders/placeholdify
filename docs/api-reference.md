# API Reference

Complete API documentation for all Placeholdify classes and methods.

## PlaceholderHandler Class

The main class for handling placeholder operations.

### Constructor

```php
public function __construct()
```

Creates a new PlaceholderHandler instance.

### Adding Placeholders

#### add()

```php
public function add(string $key, mixed $value, mixed $fallback = null): self
```

Add a single placeholder.

**Parameters:**

- `$key` - The placeholder key
- `$value` - The value to replace with
- `$fallback` - Optional fallback value if `$value` is null

**Example:**

```php
$handler->add('name', 'John Doe');
$handler->add('email', $user->email, 'N/A');
```

#### addMany()

```php
public function addMany(array $placeholders): self
```

Add multiple placeholders at once.

**Parameters:**

- `$placeholders` - Associative array of key-value pairs

**Example:**

```php
$handler->addMany([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'phone' => '+1234567890'
]);
```

#### addDate()

```php
public function addDate(string $key, mixed $date, string $format = 'Y-m-d', mixed $fallback = null): self
```

Add a formatted date placeholder.

**Parameters:**

- `$key` - The placeholder key
- `$date` - Date value (Carbon instance, string, or timestamp)
- `$format` - PHP date format string
- `$fallback` - Optional fallback value

**Example:**

```php
$handler->addDate('created_at', $user->created_at, 'F j, Y');
$handler->addDate('due_date', now()->addDays(30), 'd/m/Y');
```

#### addNullable()

```php
public function addNullable(string $key, mixed ...$values): self
```

Add placeholder with null coalescing (first non-null value wins).

**Parameters:**

- `$key` - The placeholder key
- `...$values` - Variable number of values to check

**Example:**

```php
$handler->addNullable('contact', $user->email, $user->phone, 'No contact');
```

#### addFormatted()

```php
public function addFormatted(string $key, mixed $value, string $formatter, mixed ...$args): self
```

Add placeholder with custom formatter.

**Parameters:**

- `$key` - The placeholder key
- `$value` - The value to format
- `$formatter` - The formatter name
- `...$args` - Additional arguments for the formatter

**Example:**

```php
$handler->addFormatted('price', 99.99, 'currency', 'USD');
$handler->addFormatted('name', 'john doe', 'upper');
```

#### addLazy()

```php
public function addLazy(string $key, Closure $callback, mixed $fallback = null): self
```

Add placeholder with lazy evaluation.

**Parameters:**

- `$key` - The placeholder key
- `$callback` - Closure that returns the value
- `$fallback` - Optional fallback value

**Example:**

```php
$handler->addLazy('total', function() use ($order) {
    return $order->items()->sum('amount');
});
```

#### addIf()

```php
public function addIf(bool $condition, string $key, mixed $value, mixed $elseValue = null): self
```

Add placeholder conditionally.

**Parameters:**

- `$condition` - Boolean condition
- `$key` - The placeholder key
- `$value` - Value if condition is true
- `$elseValue` - Value if condition is false

**Example:**

```php
$handler->addIf($user->is_premium, 'badge', 'Premium Member', 'Regular Member');
```

#### addFromContext()

```php
public function addFromContext(string $prefix, object $object, array $mapping): self
```

Add placeholders from object using custom mapping.

**Parameters:**

- `$prefix` - Prefix for generated placeholder keys
- `$object` - Source object
- `$mapping` - Array mapping configuration

**Example:**

```php
$handler->addFromContext('user', $user, [
    'name' => 'name',
    'email' => 'email',
    'formatted_name' => ['property' => 'name', 'formatter' => 'upper']
]);
```

### Context Management

#### registerContext()

```php
public function registerContext(string $name, array $mapping): self
```

Register a reusable context mapping.

**Parameters:**

- `$name` - Context name
- `$mapping` - Array mapping configuration

**Example:**

```php
$handler->registerContext('user', [
    'name' => 'name',
    'email' => 'email',
    'role' => fn($user) => $user->roles->pluck('name')->join(', ')
]);
```

#### useContext()

```php
public function useContext(string $name, object $object, string $prefix = ''): self
```

Use a registered context with an object.

**Parameters:**

- `$name` - Registered context name
- `$object` - Source object
- `$prefix` - Optional prefix for placeholder keys

**Example:**

```php
$handler->useContext('user', $user, 'customer');
// Creates placeholders: {customer.name}, {customer.email}, etc.
```

### Formatter Management

#### registerFormatter()

```php
public function registerFormatter(string $name, Closure $formatter): self
```

Register a custom formatter.

**Parameters:**

- `$name` - Formatter name
- `$formatter` - Closure that performs the formatting

**Example:**

```php
$handler->registerFormatter('currency', function($value, $symbol = '$') {
    return $symbol . number_format($value, 2);
});
```

### Configuration

#### setDelimiter()

```php
public function setDelimiter(string $start, string $end = null): self
```

Set custom placeholder delimiters.

**Parameters:**

- `$start` - Start delimiter (or both if $end is null)
- `$end` - Optional end delimiter

**Example:**

```php
$handler->setDelimiter('{{', '}}');
$handler->setDelimiter('[]'); // Both start and end
```

#### setFallback()

```php
public function setFallback(mixed $value): self
```

Set default fallback value for missing placeholders.

**Parameters:**

- `$value` - Fallback value

**Example:**

```php
$handler->setFallback('N/A');
```

### Processing

#### replace()

```php
public function replace(string $content): string
```

Replace placeholders in content.

**Parameters:**

- `$content` - Template content with placeholders

**Returns:**

- String with placeholders replaced

**Example:**

```php
$result = $handler->replace('Hello {name}, your email is {email}');
```

#### replaceWithModifiers()

```php
public function replaceWithModifiers(string $content): string
```

Replace placeholders with modifier support.

**Parameters:**

- `$content` - Template content with placeholders and modifiers

**Returns:**

- String with placeholders and modifiers processed

**Example:**

```php
$result = $handler->replaceWithModifiers('Hello {name|upper}, amount: {total|currency:USD}');
```

### Utility Methods

#### all()

```php
public function all(): array
```

Get all registered placeholders.

**Returns:**

- Array of all placeholder key-value pairs

#### has()

```php
public function has(string $key): bool
```

Check if a placeholder exists.

**Parameters:**

- `$key` - Placeholder key

**Returns:**

- Boolean indicating if placeholder exists

#### get()

```php
public function get(string $key, mixed $default = null): mixed
```

Get a placeholder value.

**Parameters:**

- `$key` - Placeholder key
- `$default` - Default value if key doesn't exist

**Returns:**

- Placeholder value or default

#### clear()

```php
public function clear(): self
```

Clear all placeholders.

**Returns:**

- Self for method chaining

### Static Methods

#### process()

```php
public static function process(string $content, array $placeholders, string $delimiter = '{}'): string
```

Quick static method for one-time placeholder replacement.

**Parameters:**

- `$content` - Template content
- `$placeholders` - Array of placeholder key-value pairs
- `$delimiter` - Delimiter pattern (default: '{}')

**Returns:**

- Processed content

**Example:**

```php
$result = PlaceholderHandler::process(
    'Hello {name}!',
    ['name' => 'World']
);
```

#### registerGlobalContext()

```php
public static function registerGlobalContext(string $name, array $mapping): void
```

Register a context mapping globally.

**Parameters:**

- `$name` - Context name
- `$mapping` - Array mapping configuration

#### registerGlobalFormatter()

```php
public static function registerGlobalFormatter(string $name, Closure $formatter): void
```

Register a formatter globally.

**Parameters:**

- `$name` - Formatter name
- `$formatter` - Closure that performs the formatting

## PlaceholdifyBase Class

Abstract base class for creating template classes.

### Constructor

```php
public function __construct()
```

### Abstract Methods

#### configure()

```php
abstract protected function configure(): void
```

Configure the handler (set fallbacks, register formatters, etc.).

#### build()

```php
abstract public function build(mixed $data): PlaceholderHandler
```

Build the placeholder handler with data.

**Parameters:**

- `$data` - Data to build placeholders from

**Returns:**

- Configured PlaceholderHandler instance

### Public Methods

#### generate()

```php
public function generate(mixed $data, string $template): string
```

Generate content from data and template.

**Parameters:**

- `$data` - Data to build placeholders from
- `$template` - Template content

**Returns:**

- Generated content

## Built-in Formatters

### date

Format dates using Carbon.

```php
$handler->addFormatted('created', $date, 'date', 'F j, Y');
```

### currency

Format currency values.

```php
$handler->addFormatted('price', 99.99, 'currency', 'USD');
// Output: "USD 99.99"
```

### number

Format numbers.

```php
$handler->addFormatted('count', 1234.56, 'number', 0);
// Output: "1,235"
```

### upper

Convert to uppercase.

```php
$handler->addFormatted('name', 'john doe', 'upper');
// Output: "JOHN DOE"
```

### lower

Convert to lowercase.

```php
$handler->addFormatted('email', 'USER@EXAMPLE.COM', 'lower');
// Output: "user@example.com"
```

### title

Convert to title case.

```php
$handler->addFormatted('title', 'hello world', 'title');
// Output: "Hello World"
```

## Exception Classes

### PlaceholdifyException

Base exception class for all Placeholdify-related exceptions.

### InvalidFormatterException

Thrown when an invalid formatter is used.

### InvalidContextException

Thrown when an invalid context is used.

### TemplateException

Thrown when template processing fails.

## Configuration Array Structure

### Context Mapping

```php
[
    'property_name' => 'object_property',
    'formatted_property' => [
        'property' => 'object_property',
        'formatter' => 'formatter_name',
        'args' => ['arg1', 'arg2']
    ],
    'callback_property' => fn($object) => $object->computed_value,
]
```

### Formatter Registration

```php
// Closure formatter
'formatter_name' => function($value, $arg1, $arg2) {
    return processed_value;
}

// Class method formatter
'formatter_name' => [
    'class' => FormatterClass::class,
    'method' => 'formatMethod'
]

// Invokable class
'formatter_name' => FormatterClass::class
```

## Performance Considerations

- Use `addLazy()` for expensive operations
- Register contexts once, use multiple times
- Use static `process()` method for simple one-time replacements
- Consider caching for frequently used templates
- Use appropriate data types for formatters

## Error Handling

All methods return `$this` for chaining, except:

- `replace()` and `replaceWithModifiers()` return processed strings
- `all()`, `has()`, `get()` return their respective data types
- Static methods have their own return types

Exceptions are thrown for:

- Invalid formatter names
- Invalid context names
- Template processing errors
- Invalid configuration values

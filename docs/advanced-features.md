# Advanced Features

This document covers the advanced features of Placeholdify that enable powerful template processing capabilities.

## Lazy Evaluation

Defer expensive operations until the placeholder is actually needed:

```php
$handler->addLazy('total_items', function() use ($order) {
    return $order->items()->sum('amount');
});

$handler->addLazy('user_count', function() {
    return User::count(); // Only executed if {user_count} is in template
});

$handler->addLazy('complex_calculation', function() use ($data) {
    // Expensive computation
    return $this->performComplexCalculation($data);
}, 'Calculation failed'); // With fallback
```

## Conditional Placeholders

Add placeholders based on conditions:

```php
$handler->addIf(
    $student->gpa >= 3.5,
    'honors',
    'with Honors',
    ''
);

$handler->addIf(
    $invoice->is_overdue,
    'overdue_notice',
    'This invoice is overdue!',
    'Payment is current'
);

// Multiple conditions
$handler->addIf(
    $user->role === 'admin' && $user->is_active,
    'admin_access',
    'Full administrative access',
    'Limited access'
);
```

## Template Modifiers

Use inline formatting directly in templates:

```php
$template = "Welcome {name|upper}! Your balance is {balance|currency:USD}.";

$handler = new PlaceholderHandler();
$content = $handler
    ->add('name', 'john doe')
    ->add('balance', 1234.56)
    ->replaceWithModifiers($template);

// Output: "Welcome JOHN DOE! Your balance is USD 1,234.56."
```

### Chaining Modifiers

```php
$template = "Description: {content|truncate:50|upper}";
$template = "Email: {email|lower|mask_email}";
```

### Modifier with Multiple Parameters

```php
$template = "Amount: {total|currency:MYR:2}"; // currency:symbol:decimals
$template = "Date: {created_at|date:F j, Y}";
$template = "Text: {description|truncate:100:...}";
```

## Dynamic Placeholder Generation

Generate placeholders dynamically based on data:

```php
// Generate placeholders from array
$items = ['apple', 'banana', 'orange'];
foreach ($items as $index => $item) {
    $handler->add("item_{$index}", $item);
}

// Generate from model attributes
foreach ($product->attributes as $key => $value) {
    $handler->add("product_{$key}", $value);
}

// Generate from relationships
foreach ($user->roles as $index => $role) {
    $handler->add("role_{$index}", $role->name);
}
```

## Nested Placeholder Processing

Process templates with nested placeholder structures:

```php
$template = "Hello {user.name}, your {order.type} order #{order.number} is {order.status}.";

$handler = new PlaceholderHandler();
$content = $handler
    ->useContext('user', $user, 'user')
    ->useContext('order', $order, 'order')
    ->replace($template);
```

## Template Inheritance

Create base templates that can be extended:

```php
class BaseTemplate extends PlaceholdifyBase
{
    protected function configure(): void
    {
        $this->handler
            ->addDate('generated_at', now(), 'F j, Y \a\t g:i A')
            ->add('app_name', config('app.name'))
            ->add('app_url', config('app.url'));
    }
}

class InvoiceTemplate extends BaseTemplate
{
    public function build($invoice): PlaceholderHandler
    {
        parent::configure(); // Apply base configuration

        return $this->handler
            ->useContext('invoice', $invoice, 'invoice')
            ->useContext('company', $invoice->company, 'company')
            ->addLazy('items_total', fn() => $invoice->items->sum('total'));
    }
}
```

## Conditional Blocks

Process entire blocks of content conditionally:

```php
$template = "
Welcome {name}!
{if:is_premium}
You have premium access with the following benefits:
- Priority support
- Advanced features
- Custom templates
{endif:is_premium}
{if:!is_premium}
Upgrade to premium for more features!
{endif:!is_premium}
";

$handler = new PlaceholderHandler();
$content = $handler
    ->add('name', $user->name)
    ->add('is_premium', $user->is_premium)
    ->replaceConditionalBlocks($template);
```

## Loop Processing

Process repeating sections:

```php
$template = "
Items:
{loop:items}
- {item.name}: {item.price|currency:USD}
{endloop:items}
";

$handler = new PlaceholderHandler();
$content = $handler
    ->add('items', $order->items)
    ->replaceLoops($template);
```

## Multi-language Support

Handle multi-language templates:

```php
$handler = new PlaceholderHandler();
$handler->setLocale('en');

$template = "Welcome {name|translate:welcome_message}";

$handler->registerFormatter('translate', function($value, $key) {
    return __($key, ['name' => $value]);
});
```

## Template Caching

Cache processed templates for better performance:

```php
class CachedTemplate extends PlaceholdifyBase
{
    protected $cacheKey;

    public function __construct($cacheKey = null)
    {
        parent::__construct();
        $this->cacheKey = $cacheKey;
    }

    public function generate($data, string $template): string
    {
        if ($this->cacheKey && Cache::has($this->cacheKey)) {
            return Cache::get($this->cacheKey);
        }

        $content = $this->build($data)->replace($template);

        if ($this->cacheKey) {
            Cache::put($this->cacheKey, $content, 3600); // 1 hour
        }

        return $content;
    }
}
```

## Validation and Error Handling

Validate placeholders and handle errors gracefully:

```php
class ValidatedTemplate extends PlaceholdifyBase
{
    protected $requiredPlaceholders = [];

    public function setRequired(array $placeholders): self
    {
        $this->requiredPlaceholders = $placeholders;
        return $this;
    }

    public function generate($data, string $template): string
    {
        $handler = $this->build($data);

        // Validate required placeholders
        foreach ($this->requiredPlaceholders as $placeholder) {
            if (!$handler->has($placeholder)) {
                throw new InvalidArgumentException("Required placeholder '{$placeholder}' is missing");
            }
        }

        // Check for unused placeholders in template
        $templatePlaceholders = $this->extractPlaceholders($template);
        $unusedPlaceholders = array_diff($templatePlaceholders, array_keys($handler->all()));

        if (!empty($unusedPlaceholders)) {
            Log::warning('Unused placeholders found', $unusedPlaceholders);
        }

        return $handler->replace($template);
    }

    protected function extractPlaceholders(string $template): array
    {
        preg_match_all('/\{([^}]+)\}/', $template, $matches);
        return $matches[1];
    }
}
```

## Performance Optimization

Optimize performance for large-scale template processing:

```php
class OptimizedHandler extends PlaceholderHandler
{
    protected $compiled = [];

    public function compile(string $template): array
    {
        $hash = md5($template);

        if (isset($this->compiled[$hash])) {
            return $this->compiled[$hash];
        }

        // Pre-compile template structure
        $this->compiled[$hash] = $this->parseTemplate($template);

        return $this->compiled[$hash];
    }

    public function replaceCompiled(string $template): string
    {
        $compiled = $this->compile($template);

        // Use compiled structure for faster replacement
        return $this->processCompiled($compiled);
    }
}
```

## Event Hooks

Hook into the placeholder processing lifecycle:

```php
class EventedHandler extends PlaceholderHandler
{
    protected $hooks = [];

    public function onBeforeReplace(callable $callback): self
    {
        $this->hooks['before_replace'][] = $callback;
        return $this;
    }

    public function onAfterReplace(callable $callback): self
    {
        $this->hooks['after_replace'][] = $callback;
        return $this;
    }

    public function replace(string $content): string
    {
        $this->fireHooks('before_replace', [$content, $this]);

        $result = parent::replace($content);

        $this->fireHooks('after_replace', [$result, $this]);

        return $result;
    }

    protected function fireHooks(string $event, array $args): void
    {
        foreach ($this->hooks[$event] ?? [] as $callback) {
            call_user_func_array($callback, $args);
        }
    }
}
```

# Context System

The context system allows you to register reusable mappings for your models, making it easy to extract and map data consistently across your application. Placeholdify supports both modern contract-based contexts and legacy array-based contexts.

## Context Classes (Recommended)

### What are Context Classes?

Context classes implement the `ContextInterface` and provide type-safe, reusable mappings for specific object types. They offer better organization, validation, and IDE support compared to array-based contexts.

### Built-in UserContext

Placeholdify includes a built-in `UserContext` that provides common user-related mappings:

```php
use CleaniqueCoders\Placeholdify\PlaceholderHandler;

$handler = new PlaceholderHandler();

// The UserContext is automatically registered from config
$handler->useContextWithObject('user', $user, 'customer');

// Available placeholders:
// {customer.id}, {customer.name}, {customer.email}
// {customer.full_name}, {customer.initials}, {customer.display_name}
// {customer.created_at}, {customer.is_verified}, etc.
```

### Creating Custom Context Classes

```php
namespace App\Contexts;

use CleaniqueCoders\Placeholdify\Contracts\ContextInterface;

class OrderContext implements ContextInterface
{
    public function getName(): string
    {
        return 'order';
    }

    public function getMapping(): array
    {
        return [
            'id' => 'id',
            'number' => 'order_number',
            'total' => [
                'property' => 'total_amount',
                'formatter' => 'currency'
            ],
            'status' => 'status',
            'customer_name' => 'customer.name',
            'items_count' => fn($order) => $order->items->count(),
            'formatted_date' => [
                'property' => 'created_at',
                'formatter' => 'date'
            ],
        ];
    }

    public function canProcess(mixed $object): bool
    {
        return $object instanceof \App\Models\Order;
    }

    public function getSupportedTypes(): string|array
    {
        return 'App\Models\Order';
    }
}
```

### Registering Context Classes

Register in your configuration file:

```php
// config/placeholdify.php
'context_classes' => [
    \CleaniqueCoders\Placeholdify\Contexts\UserContext::class,
    \App\Contexts\OrderContext::class,
    \App\Contexts\CustomerContext::class,
],
```

Or register manually:

```php
$handler->registerContext(new \App\Contexts\OrderContext());
```

### Using Context Classes

```php
```php
// Use with object validation
$handler->useContext('order', $order, 'order');

// The context will validate if it can process the object
// and apply the mapping automatically
```

## Context System Methods

### Available Methods for Context Classes

- `registerContext(ContextInterface $context)` - Register a context instance
- `useContext(string $name, object $object, string $prefix = '')` - Use a registered context
- `hasContext(string $name)` - Check if a context is registered
- `getRegisteredContexts()` - Get all registered context names
- `getRegisteredContextInstances()` - Get all registered context instance names

## Context Validation

All contexts registered must implement `ContextInterface` and provide validation through the `canProcess()` method.

```php
// The context automatically validates if it can process the object
$handler->useContext('user', $user, 'customer');

// If validation fails, no placeholders are added
$handler->useContext('user', $nonUserObject, 'customer'); // No placeholders added
```

## Dynamic Context Loading

Load contexts dynamically based on conditions:

```php
$contextName = $user->role === 'admin' ? 'admin_user' : 'user';
$handler->useContext($contextName, $user, 'current_user');
```
```

## Legacy Array-based Contexts

### Registering a Context (Legacy)

```php
$handler->registerContextMapping('student', [
    'name' => 'student_name',
    'email' => 'email',
    'matric' => 'matric_number',
    'program' => 'program.name', // Nested relationships
]);
```

### Using a Context

```php
$handler->useContext('student', $student, 'student');

// Access as: {student.name}, {student.email}, {student.matric}
```

## Advanced Context Mapping

### Using Closures

```php
$handler->registerContextMapping('user', [
    'name' => 'name',
    'email' => 'email',
    'role' => fn($user) => $user->roles->pluck('name')->join(', '),
    'full_name' => fn($user) => $user->first_name . ' ' . $user->last_name,
]);
```

### Nested Object Access

```php
$handler->registerContextMapping('order', [
    'number' => 'order_number',
    'total' => 'total_amount',
    'customer_name' => 'customer.name',
    'customer_email' => 'customer.email',
    'shipping_address' => 'shipping.address.full_address',
]);
```

### With Formatters

```php
$handler->registerContextMapping('invoice', [
    'number' => 'invoice_number',
    'total' => ['property' => 'total', 'formatter' => 'currency', 'args' => ['MYR']],
    'date' => ['property' => 'created_at', 'formatter' => 'date', 'args' => ['d/m/Y']],
]);
```

## Global Contexts

Register contexts globally in your service provider for reuse across the application:

```php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use CleaniqueCoders\Placeholdify\PlaceholderHandler;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        PlaceholderHandler::registerGlobalContext('user', [
            'name' => 'name',
            'email' => 'email',
            'role' => fn($user) => $user->roles->pluck('name')->join(', '),
            'avatar' => fn($user) => $user->getFirstMediaUrl('avatar'),
        ]);

        PlaceholderHandler::registerGlobalContext('company', [
            'name' => 'name',
            'address' => 'address',
            'phone' => 'phone',
            'email' => 'contact_email',
            'logo' => fn($company) => $company->getFirstMediaUrl('logo'),
        ]);
    }
}
```

## Context from Objects

Extract data from objects with custom mappings:

```php
$handler->addFromContext('student', $student, [
    'name' => 'student_name',
    'email' => 'email',
    'formatted_name' => ['property' => 'student_name', 'formatter' => 'upper'],
    'full_details' => fn($s) => $s->name . ' (' . $s->matric . ')',
    'gpa' => ['property' => 'gpa', 'formatter' => 'number', 'args' => [2]],
]);
```

## Configuration Context

Define contexts in your configuration file:

```php
// config/placeholdify.php
return [
    'contexts' => [
        'user' => [
            'name' => 'name',
            'email' => 'email',
            'created' => ['property' => 'created_at', 'formatter' => 'date', 'args' => ['F j, Y']],
        ],
        'product' => [
            'name' => 'name',
            'price' => ['property' => 'price', 'formatter' => 'currency', 'args' => ['USD']],
            'category' => 'category.name',
        ],
    ],
];
```

## Real-World Examples

### Student Context

```php
PlaceholderHandler::registerGlobalContext('student', [
    'name' => 'student_name',
    'matric' => 'matric_number',
    'email' => 'email',
    'phone' => 'phone_number',
    'program' => 'program.name',
    'faculty' => 'program.faculty.name',
    'semester' => 'current_semester',
    'gpa' => ['property' => 'gpa', 'formatter' => 'number', 'args' => [2]],
    'status' => fn($student) => $student->is_active ? 'Active' : 'Inactive',
    'full_name' => fn($student) => $student->student_name . ' (' . $student->matric_number . ')',
]);
```

### Invoice Context

```php
PlaceholderHandler::registerGlobalContext('invoice', [
    'number' => 'invoice_number',
    'subtotal' => ['property' => 'subtotal', 'formatter' => 'currency', 'args' => ['MYR']],
    'tax' => ['property' => 'tax_amount', 'formatter' => 'currency', 'args' => ['MYR']],
    'total' => ['property' => 'total', 'formatter' => 'currency', 'args' => ['MYR']],
    'date' => ['property' => 'created_at', 'formatter' => 'date', 'args' => ['d/m/Y']],
    'due_date' => ['property' => 'due_date', 'formatter' => 'date', 'args' => ['d/m/Y']],
    'status' => 'status',
    'customer_name' => 'customer.name',
    'customer_email' => 'customer.email',
    'items_count' => fn($invoice) => $invoice->items()->count(),
]);
```

### Company Context

```php
PlaceholderHandler::registerGlobalContext('company', [
    'name' => 'name',
    'registration' => 'registration_number',
    'address' => fn($company) => implode(', ', array_filter([
        $company->street,
        $company->city,
        $company->state,
        $company->postal_code,
    ])),
    'phone' => 'phone',
    'email' => 'email',
    'website' => 'website',
    'established' => ['property' => 'established_at', 'formatter' => 'date', 'args' => ['Y']],
]);
```

## Usage in Templates

```php
// Using multiple contexts
$handler = new PlaceholderHandler();
$content = $handler
    ->useContext('student', $student, 'student')
    ->useContext('company', $company, 'company')
    ->useContext('invoice', $invoice, 'invoice')
    ->replace($template);
```

Template example:

```text
INVOICE #{invoice.number}

From:
{company.name}
{company.address}
Phone: {company.phone}
Email: {company.email}

To:
{student.name} ({student.matric})
{student.email}
Program: {student.program}

Invoice Details:
Date: {invoice.date}
Due Date: {invoice.due_date}
Subtotal: {invoice.subtotal}
Tax: {invoice.tax}
Total: {invoice.total}

Status: {invoice.status}

## Context Classes vs Array-based Contexts

### Advantages of Context Classes

Since Placeholdify now only supports context classes (implementing `ContextInterface`), you get these benefits:

1. **Type Safety**: Context classes provide better type checking and IDE support
2. **Validation**: Built-in object validation with `canProcess()` method
3. **Reusability**: Easy to share across different parts of your application
4. **Organization**: Better code organization and maintainability
5. **Testing**: Easier to unit test context logic
6. **Documentation**: Self-documenting with method signatures and return types
```

## Built-in UserContext Reference

The built-in `UserContext` provides these placeholders:

| Placeholder | Description | Source |
|-------------|-------------|---------|
| `id` | User ID | `$user->id` |
| `name` | User name | `$user->name` |
| `email` | User email | `$user->email` |
| `first_name` | First name | `$user->first_name` |
| `last_name` | Last name | `$user->last_name` |
| `full_name` | Computed full name | `first_name + last_name` |
| `initials` | User initials | Computed from name |
| `display_name` | Best available name | `display_name` or fallback |
| `avatar` | Avatar URL | `$user->avatar` |
| `profile_photo` | Profile photo path | `$user->profile_photo_path` |
| `phone` | Phone number | `$user->phone` |
| `created_at` | Creation date | Formatted with date formatter |
| `updated_at` | Update date | Formatted with date formatter |
| `email_verified_at` | Verification date | Formatted with date formatter |
| `is_verified` | Verification status | "Yes" or "No" |
| `profile_url` | Profile URL | Generated route or fallback |

## Best Practices

1. **Prefer context classes** for new development
2. **Use meaningful prefixes** when applying contexts
3. **Validate objects** before processing when possible
4. **Document your mappings** in context classes
5. **Test context logic** thoroughly
6. **Keep contexts focused** on single responsibilities
7. **Use formatters** for consistent data presentation

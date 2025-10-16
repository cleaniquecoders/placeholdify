# Artisan Commands

Placeholdify provides several Artisan commands to help you work with templates and demonstrate package functionality.

## Template Creation Command

The `placeholdify:make-template` command generates new template classes with customizable stubs.

### Usage

```bash
php artisan placeholdify:make-template {name} {--type=basic} {--path=app/Services/Templates} {--list}
```

### Parameters

- `name`: The name of your template class (required)
- `--type`: Template type (optional, default: basic)
- `--path`: Directory path where template will be created (optional, default: app/Services/Templates)
- `--list`: List all available template types

### Examples

#### Create a Basic Template

```bash
php artisan placeholdify:make-template NewsletterTemplate
```

This creates `app/Services/Templates/NewsletterTemplate.php`:

```php
<?php

namespace App\Services\Templates;

use CleaniqueCoders\Placeholdify\PlaceholderHandler;
use CleaniqueCoders\Placeholdify\PlaceholdifyBase;

class NewsletterTemplate extends PlaceholdifyBase
{
    protected function configure(): void
    {
        $this->handler->setFallback('N/A');
    }

    public function build(mixed $data): PlaceholderHandler
    {
        return $this->handler
            ->add('title', $data->title ?? '')
            ->add('content', $data->content ?? '')
            ->addDate('date', now());
    }
}
```

#### Create Different Template Types

```bash
# Letter template
php artisan placeholdify:make-template WelcomeLetter --type=letter

# Invoice template
php artisan placeholdify:make-template SalesInvoice --type=invoice

# Email template
php artisan placeholdify:make-template WelcomeEmail --type=email

# Custom path
php artisan placeholdify:make-template ReportTemplate --path=app/Reports/Templates
```

#### List Available Templates

```bash
php artisan placeholdify:make-template --list
```

Output:

```
Available template types:
- basic: Basic template with common placeholders
- letter: Formal letter template with sender/recipient contexts
- invoice: Invoice template with financial formatters
- email: Email template with user/company contexts
```

### Publishing and Customizing Stubs

Publish the template stubs to customize them:

```bash
php artisan vendor:publish --tag=placeholdify-stubs
```

This creates `stubs/placeholdify/` directory with customizable stub files:

```
stubs/
└── placeholdify/
    ├── template.basic.stub
    ├── template.email.stub
    ├── template.invoice.stub
    └── template.letter.stub
```

#### Benefits of `stubs/placeholdify/` Structure

1. **Namespace isolation**: Prevents conflicts with main application stubs
2. **Clear ownership**: Obvious that these stubs belong to Placeholdify package
3. **Organization**: Groups related stubs together
4. **Future-proof**: Allows for additional stub categories if needed

### Creating Custom Stubs

Create custom template stubs by adding new files to `stubs/placeholdify/`:

#### Example: Report Template Stub

Create `stubs/placeholdify/template.report.stub`:

```php
<?php

namespace {{ namespace }};

use CleaniqueCoders\Placeholdify\PlaceholderHandler;
use CleaniqueCoders\Placeholdify\PlaceholdifyBase;

/**
 * {{ class }} Report Template
 */
class {{ class }} extends PlaceholdifyBase
{
    protected function configure(): void
    {
        $this->handler->setFallback('N/A');

        // Register report-specific formatters
        $this->handler->registerFormatter('percentage', function($value, $decimals = 2) {
            return number_format($value * 100, $decimals) . '%';
        });
    }

    public function build(mixed $data): PlaceholderHandler
    {
        return $this->handler
            ->add('report_title', $data->title ?? '')
            ->add('department', $data->department ?? '')
            ->addDate('period_start', $data->period_start ?? now()->startOfMonth(), 'F j, Y')
            ->addDate('period_end', $data->period_end ?? now()->endOfMonth(), 'F j, Y')
            ->addDate('generated_at', now(), 'F j, Y \a\t g:i A')
            ->addLazy('summary', function() use ($data) {
                return $this->generateSummary($data);
            });
    }

    protected function generateSummary($data): string
    {
        // Generate report summary logic
        return 'Report summary generated on ' . now()->format('Y-m-d H:i:s');
    }
}
```

Then use it:

```bash
php artisan placeholdify:make-template MonthlyReport --type=report
```

## Demo Command

The `placeholdify:demo` command demonstrates package functionality with examples.

### Usage

```bash
# Show interactive demo
php artisan placeholdify:demo

# Process custom template with data
php artisan placeholdify:demo --template="Hello {name}!" --data='{"name":"World"}'

# Show specific example
php artisan placeholdify:demo --example=invoice

# List all examples
php artisan placeholdify:demo --list
```

### Demo Examples

The demo command includes several built-in examples:

#### Basic Example

```bash
php artisan placeholdify:demo --example=basic
```

Shows basic placeholder replacement with user data.

#### Invoice Example

```bash
php artisan placeholdify:demo --example=invoice
```

Demonstrates invoice generation with:

- Currency formatting
- Date formatting
- Customer context
- Item calculations

#### Letter Example

```bash
php artisan placeholdify:demo --example=letter
```

Shows formal letter generation with:

- Sender/recipient contexts
- Date formatting
- Reference numbers

#### Context Example

```bash
php artisan placeholdify:demo --example=context
```

Demonstrates context system with:

- Model mapping
- Nested relationships
- Custom formatters

### Custom Demo Data

Process your own templates:

```bash
# Simple template
php artisan placeholdify:demo \
  --template="Welcome {name}, today is {date|date:F j, Y}" \
  --data='{"name":"John","date":"2024-01-15"}'

# Complex template with formatters
php artisan placeholdify:demo \
  --template="Invoice {number}: {total|currency:USD}" \
  --data='{"number":"INV-001","total":1234.56}'
```

## Configuration Commands

### Publish Configuration

```bash
php artisan vendor:publish --tag=placeholdify-config
```

Creates `config/placeholdify.php` with default settings:

```php
return [
    'delimiter' => [
        'start' => '{',
        'end' => '}',
    ],
    'fallback' => 'N/A',
    'formatters' => [
        // Global formatters
    ],
    'contexts' => [
        // Global contexts
    ],
];
```

### Publish All

```bash
php artisan vendor:publish --provider="CleaniqueCoders\Placeholdify\PlaceholdifyServiceProvider"
```

Publishes all package assets:

- Configuration file
- Template stubs
- Example files

## Testing Commands

### Running Package Tests

```bash
# Run all tests
composer test

# Run with coverage
composer test:coverage

# Run specific test
vendor/bin/pest tests/PlaceholderHandlerTest.php
```

### Custom Test Commands

You can create custom commands to test your templates:

```php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Templates\InvoiceTemplate;

class TestInvoiceTemplate extends Command
{
    protected $signature = 'test:invoice-template {invoice_id}';
    protected $description = 'Test invoice template generation';

    public function handle()
    {
        $invoiceId = $this->argument('invoice_id');
        $invoice = Invoice::findOrFail($invoiceId);

        $template = new InvoiceTemplate();
        $templateContent = view('templates.invoice')->render();

        $content = $template->generate($invoice, $templateContent);

        $this->info('Generated Invoice Template:');
        $this->line($content);
    }
}
```

## Helpful Tips

### Tab Completion

Add command aliases for faster usage:

```bash
# Add to ~/.zshrc or ~/.bashrc
alias pt='php artisan placeholdify:make-template'
alias pd='php artisan placeholdify:demo'
```

### IDE Integration

Most IDEs support Artisan command completion. Configure your IDE to recognize the custom commands for better development experience.

### Debugging

Use the demo command to test formatters and contexts:

```bash
# Test custom formatter
php artisan placeholdify:demo \
  --template="{value|custom_formatter:param}" \
  --data='{"value":"test"}'

# Test context mapping
php artisan placeholdify:demo \
  --template="{user.name} - {user.email}" \
  --data='{"user":{"name":"John","email":"john@example.com"}}'
```

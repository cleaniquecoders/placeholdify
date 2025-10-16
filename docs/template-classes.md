# Template Classes

Learn how to create and use dedicated template classes for organized and reusable template processing.

## Creating Template Classes

Extend the `PlaceholdifyBase` class to create dedicated template processors:

```php
namespace App\Services\Templates;

use CleaniqueCoders\Placeholdify\PlaceholderHandler;
use CleaniqueCoders\Placeholdify\PlaceholdifyBase;

class InvoiceTemplate extends PlaceholdifyBase
{
    protected function configure(): void
    {
        $this->handler->setFallback('N/A');

        // Register custom formatters
        $this->handler->registerFormatter('money', function($value, $currency = 'MYR') {
            return $currency . ' ' . number_format($value, 2);
        });
    }

    public function build($invoice): PlaceholderHandler
    {
        return $this->handler
            ->add('invoice_no', $invoice->invoice_number)
            ->addFormatted('total', $invoice->total, 'money', 'MYR')
            ->addFormatted('subtotal', $invoice->subtotal, 'money', 'MYR')
            ->addDate('invoice_date', $invoice->created_at, 'd/m/Y')
            ->addDate('due_date', $invoice->due_date, 'd/m/Y')
            ->useContext('customer', $invoice->customer, 'customer')
            ->addLazy('items_list', function() use ($invoice) {
                return $invoice->items
                    ->map(fn($i) => $i->description . ' - RM' . number_format($i->amount, 2))
                    ->join("\n");
            });
    }
}
```

## Using Template Classes

```php
use App\Services\Templates\InvoiceTemplate;

$template = new InvoiceTemplate();
$content = $template->generate($invoice, $templateContent);

// Or get the handler for more control
$handler = $template->build($invoice);
$content = $handler->replace($templateContent);
```

## Template Class Examples

### Letter Template

```php
namespace App\Services\Templates;

class LetterTemplate extends PlaceholdifyBase
{
    protected function configure(): void
    {
        $this->handler
            ->setFallback('N/A')
            ->addDate('today', now(), 'F j, Y');
    }

    public function build($data): PlaceholderHandler
    {
        return $this->handler
            ->useContext('sender', $data['sender'], 'sender')
            ->useContext('recipient', $data['recipient'], 'recipient')
            ->add('subject', $data['subject'])
            ->add('body', $data['body'])
            ->add('reference_no', $data['reference_no'] ?? 'N/A');
    }
}
```

### Permit Template

```php
namespace App\Services\Templates;

class PermitTemplate extends PlaceholdifyBase
{
    protected function configure(): void
    {
        $this->handler->setFallback('N/A');
    }

    public function build($formAppliance): PlaceholderHandler
    {
        return $this->handler
            ->add('permitNo', $this->generatePermitNo($formAppliance))
            ->addDate('issued_at', now())
            ->addDate('expires_at', now()->addYear())
            ->useContext('student', $formAppliance->student, 'student')
            ->useContext('appliance', $formAppliance, 'appliance')
            ->add('approved_by', $formAppliance->approvedBy->name ?? 'System');
    }

    protected function generatePermitNo($formAppliance): string
    {
        return 'PERMIT-' . now()->year . '-' . str_pad($formAppliance->id, 6, '0', STR_PAD_LEFT);
    }
}
```

### Email Template

```php
namespace App\Services\Templates;

class EmailTemplate extends PlaceholdifyBase
{
    protected function configure(): void
    {
        $this->handler
            ->setFallback('')
            ->add('app_name', config('app.name'))
            ->add('app_url', config('app.url'))
            ->addDate('current_year', now(), 'Y');
    }

    public function build($data): PlaceholderHandler
    {
        return $this->handler
            ->useContext('user', $data['user'], 'user')
            ->add('subject', $data['subject'])
            ->add('greeting', $data['greeting'] ?? 'Hello')
            ->add('content', $data['content'])
            ->add('action_text', $data['action_text'] ?? null)
            ->add('action_url', $data['action_url'] ?? null);
    }
}
```

### Certificate Template

```php
namespace App\Services\Templates;

class CertificateTemplate extends PlaceholdifyBase
{
    protected function configure(): void
    {
        $this->handler
            ->setFallback('N/A')
            ->addDate('issued_date', now(), 'F j, Y');
    }

    public function build($certificate): PlaceholderHandler
    {
        return $this->handler
            ->add('certificate_no', $certificate->certificate_number)
            ->add('title', $certificate->title)
            ->useContext('recipient', $certificate->recipient, 'recipient')
            ->useContext('issuer', $certificate->issuer, 'issuer')
            ->add('course_name', $certificate->course->name)
            ->addDate('completion_date', $certificate->completed_at, 'F j, Y')
            ->addIf(
                $certificate->grade,
                'grade_text',
                "with a grade of {$certificate->grade}",
                ''
            );
    }
}
```

## Template Inheritance

Create base templates for common functionality:

```php
namespace App\Services\Templates;

abstract class BaseOfficialTemplate extends PlaceholdifyBase
{
    protected function configure(): void
    {
        $this->handler
            ->setFallback('N/A')
            ->add('company_name', config('company.name'))
            ->add('company_address', config('company.address'))
            ->add('company_phone', config('company.phone'))
            ->add('company_email', config('company.email'))
            ->addDate('generated_at', now(), 'F j, Y \a\t g:i A');
    }
}

class OfficialLetterTemplate extends BaseOfficialTemplate
{
    public function build($data): PlaceholderHandler
    {
        return $this->handler
            ->add('letter_ref', $data['reference'])
            ->add('subject', $data['subject'])
            ->useContext('recipient', $data['recipient'], 'to')
            ->add('content', $data['content']);
    }
}
```

## Configurable Templates

Create templates that can be configured:

```php
namespace App\Services\Templates;

class ConfigurableTemplate extends PlaceholdifyBase
{
    protected $dateFormat = 'F j, Y';
    protected $currencySymbol = 'MYR';
    protected $includeFooter = true;

    public function setDateFormat(string $format): self
    {
        $this->dateFormat = $format;
        return $this;
    }

    public function setCurrency(string $symbol): self
    {
        $this->currencySymbol = $symbol;
        return $this;
    }

    public function includeFooter(bool $include = true): self
    {
        $this->includeFooter = $include;
        return $this;
    }

    protected function configure(): void
    {
        $this->handler->registerFormatter('money', function($value) {
            return $this->currencySymbol . ' ' . number_format($value, 2);
        });
    }

    public function build($data): PlaceholderHandler
    {
        $handler = $this->handler
            ->addDate('today', now(), $this->dateFormat)
            ->add('title', $data['title']);

        if ($this->includeFooter) {
            $handler->add('footer', 'Generated by ' . config('app.name'));
        }

        return $handler;
    }
}

// Usage
$template = (new ConfigurableTemplate())
    ->setDateFormat('d/m/Y')
    ->setCurrency('USD')
    ->includeFooter(false);
```

## Template Validation

Add validation to ensure data integrity:

```php
namespace App\Services\Templates;

class ValidatedTemplate extends PlaceholdifyBase
{
    protected $requiredFields = [];
    protected $validationRules = [];

    protected function setRequired(array $fields): void
    {
        $this->requiredFields = $fields;
    }

    protected function setValidationRules(array $rules): void
    {
        $this->validationRules = $rules;
    }

    public function generate($data, string $template): string
    {
        $this->validateData($data);
        return parent::generate($data, $template);
    }

    protected function validateData($data): void
    {
        // Check required fields
        foreach ($this->requiredFields as $field) {
            if (!isset($data[$field])) {
                throw new \InvalidArgumentException("Required field '{$field}' is missing");
            }
        }

        // Apply validation rules
        foreach ($this->validationRules as $field => $rule) {
            if (isset($data[$field]) && !$this->validateField($data[$field], $rule)) {
                throw new \InvalidArgumentException("Field '{$field}' does not meet validation requirements");
            }
        }
    }

    protected function validateField($value, $rule): bool
    {
        switch ($rule) {
            case 'email':
                return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
            case 'numeric':
                return is_numeric($value);
            case 'date':
                return strtotime($value) !== false;
            default:
                return true;
        }
    }
}
```

## Template Factory

Create a factory for managing multiple template types:

```php
namespace App\Services\Templates;

class TemplateFactory
{
    protected $templates = [
        'invoice' => InvoiceTemplate::class,
        'letter' => LetterTemplate::class,
        'permit' => PermitTemplate::class,
        'email' => EmailTemplate::class,
        'certificate' => CertificateTemplate::class,
    ];

    public function create(string $type): PlaceholdifyBase
    {
        if (!isset($this->templates[$type])) {
            throw new \InvalidArgumentException("Unknown template type: {$type}");
        }

        $class = $this->templates[$type];
        return new $class();
    }

    public function register(string $type, string $class): void
    {
        $this->templates[$type] = $class;
    }

    public function getAvailableTypes(): array
    {
        return array_keys($this->templates);
    }
}

// Usage
$factory = new TemplateFactory();
$invoiceTemplate = $factory->create('invoice');
$content = $invoiceTemplate->generate($invoice, $templateContent);
```

## Testing Template Classes

```php
namespace Tests\Unit\Templates;

use Tests\TestCase;
use App\Services\Templates\InvoiceTemplate;

class InvoiceTemplateTest extends TestCase
{
    public function test_invoice_template_generates_content()
    {
        $invoice = $this->createMockInvoice();
        $template = new InvoiceTemplate();

        $content = $template->generate($invoice, 'Invoice: {invoice_no}, Total: {total}');

        $this->assertStringContains('Invoice: INV-001', $content);
        $this->assertStringContains('Total: MYR 1,000.00', $content);
    }

    private function createMockInvoice()
    {
        // Create mock invoice data
        return (object) [
            'invoice_number' => 'INV-001',
            'total' => 1000.00,
            'created_at' => now(),
        ];
    }
}
```

# Real World Examples

This document provides comprehensive real-world examples of using Placeholdify in various scenarios.

## Student Permit System

### Permit Application Template

```php
namespace App\Services\Templates;

use CleaniqueCoders\Placeholdify\PlaceholdifyBase;
use CleaniqueCoders\Placeholdify\PlaceholderHandler;

class PermitTemplate extends PlaceholdifyBase
{
    protected function configure(): void
    {
        $this->handler->setFallback('N/A');

        $this->handler->registerContext('student', [
            'name' => 'student_name',
            'matric' => 'matric_number',
            'email' => 'email',
            'program' => 'program.name',
            'faculty' => 'program.faculty.name',
        ]);

        $this->handler->registerContext('appliance', [
            'type' => 'type',
            'brand' => 'brand',
            'model' => 'model',
            'serial' => 'serial_number',
            'power' => 'power_rating',
        ]);
    }

    public function build($application): PlaceholderHandler
    {
        return $this->handler
            ->add('permit_no', $this->generatePermitNumber($application))
            ->addDate('issued_date', now(), 'F j, Y')
            ->addDate('expiry_date', now()->addYear(), 'F j, Y')
            ->useContext('student', $application->student, 'student')
            ->useContext('appliance', $application->appliance, 'appliance')
            ->add('room_no', $application->room_number)
            ->add('block', $application->block)
            ->add('approval_status', $application->status)
            ->add('approved_by', $application->approvedBy->name ?? 'System');
    }

    protected function generatePermitNumber($application): string
    {
        $year = now()->year;
        $faculty = strtoupper(substr($application->student->program->faculty->code, 0, 3));
        $sequence = str_pad($application->id, 4, '0', STR_PAD_LEFT);

        return "PERMIT/{$year}/{$faculty}/{$sequence}";
    }
}
```

### Controller Implementation

```php
namespace App\Http\Controllers;

use App\Models\ApplianceApplication;
use App\Services\Templates\PermitTemplate;
use Illuminate\Http\Request;

class PermitController extends Controller
{
    public function generatePermit($id)
    {
        $application = ApplianceApplication::with([
            'student.program.faculty',
            'appliance',
            'approvedBy'
        ])->findOrFail($id);

        $template = new PermitTemplate();
        $templateContent = view('templates.permit')->render();

        $content = $template->generate($application, $templateContent);

        return view('permits.preview', [
            'content' => $content,
            'application' => $application,
        ]);
    }

    public function downloadPermit($id)
    {
        $application = ApplianceApplication::findOrFail($id);
        $template = new PermitTemplate();
        $templateContent = view('templates.permit')->render();

        $content = $template->generate($application, $templateContent);

        $pdf = app('dompdf.wrapper')->loadHTML($content);

        return $pdf->download("permit_{$application->permit_number}.pdf");
    }
}
```

### Template File

```html
<!-- resources/views/templates/permit.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Appliance Usage Permit</title>
    <style>
        .header { text-align: center; margin-bottom: 30px; }
        .permit-details { margin: 20px 0; }
        .signature-section { margin-top: 50px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>APPLIANCE USAGE PERMIT</h1>
        <h2>University Technology MARA</h2>
    </div>

    <div class="permit-details">
        <p><strong>Permit No:</strong> {permit_no}</p>
        <p><strong>Issue Date:</strong> {issued_date}</p>
        <p><strong>Expiry Date:</strong> {expiry_date}</p>

        <h3>Student Information</h3>
        <p><strong>Name:</strong> {student.name}</p>
        <p><strong>Matric No:</strong> {student.matric}</p>
        <p><strong>Email:</strong> {student.email}</p>
        <p><strong>Program:</strong> {student.program}</p>
        <p><strong>Faculty:</strong> {student.faculty}</p>

        <h3>Accommodation Details</h3>
        <p><strong>Block:</strong> {block}</p>
        <p><strong>Room No:</strong> {room_no}</p>

        <h3>Appliance Information</h3>
        <p><strong>Type:</strong> {appliance.type}</p>
        <p><strong>Brand:</strong> {appliance.brand}</p>
        <p><strong>Model:</strong> {appliance.model}</p>
        <p><strong>Serial Number:</strong> {appliance.serial}</p>
        <p><strong>Power Rating:</strong> {appliance.power}W</p>
    </div>

    <div class="signature-section">
        <p><strong>Approved by:</strong> {approved_by}</p>
        <p><strong>Status:</strong> {approval_status}</p>
    </div>
</body>
</html>
```

## Invoice Generation System

### Invoice Template Class

```php
namespace App\Services\Templates;

use CleaniqueCoders\Placeholdify\PlaceholdifyBase;
use CleaniqueCoders\Placeholdify\PlaceholderHandler;

class InvoiceTemplate extends PlaceholdifyBase
{
    protected function configure(): void
    {
        $this->handler->setFallback('N/A');

        // Register currency formatter
        $this->handler->registerFormatter('currency', function($value, $currency = 'MYR') {
            return $currency . ' ' . number_format($value, 2);
        });

        // Register contexts
        $this->handler->registerContext('company', [
            'name' => 'name',
            'address' => fn($company) => $this->formatAddress($company),
            'phone' => 'phone',
            'email' => 'email',
            'registration' => 'registration_number',
        ]);

        $this->handler->registerContext('customer', [
            'name' => 'name',
            'address' => fn($customer) => $this->formatAddress($customer),
            'phone' => 'phone',
            'email' => 'email',
        ]);
    }

    public function build($invoice): PlaceholderHandler
    {
        return $this->handler
            ->add('invoice_number', $invoice->invoice_number)
            ->addDate('invoice_date', $invoice->created_at, 'F j, Y')
            ->addDate('due_date', $invoice->due_date, 'F j, Y')
            ->addFormatted('subtotal', $invoice->subtotal, 'currency', 'MYR')
            ->addFormatted('tax_amount', $invoice->tax_amount, 'currency', 'MYR')
            ->addFormatted('discount', $invoice->discount, 'currency', 'MYR')
            ->addFormatted('total', $invoice->total, 'currency', 'MYR')
            ->useContext('company', $invoice->company, 'company')
            ->useContext('customer', $invoice->customer, 'customer')
            ->add('payment_terms', $invoice->payment_terms)
            ->add('notes', $invoice->notes)
            ->addLazy('items_list', function() use ($invoice) {
                return $this->generateItemsList($invoice);
            });
    }

    protected function formatAddress($entity): string
    {
        return implode(', ', array_filter([
            $entity->street,
            $entity->city,
            $entity->state,
            $entity->postal_code,
        ]));
    }

    protected function generateItemsList($invoice): string
    {
        return $invoice->items->map(function($item) {
            return sprintf(
                "%s - Qty: %d - Rate: RM%.2f - Total: RM%.2f",
                $item->description,
                $item->quantity,
                $item->rate,
                $item->total
            );
        })->join("\n");
    }
}
```

### Service Class

```php
namespace App\Services;

use App\Models\Invoice;
use App\Services\Templates\InvoiceTemplate;

class InvoiceService
{
    public function generateInvoice(Invoice $invoice): string
    {
        $template = new InvoiceTemplate();
        $templateContent = $this->getInvoiceTemplate();

        return $template->generate($invoice, $templateContent);
    }

    public function sendInvoiceEmail(Invoice $invoice): void
    {
        $content = $this->generateInvoice($invoice);

        Mail::to($invoice->customer->email)->send(
            new InvoiceMail($content, $invoice)
        );
    }

    protected function getInvoiceTemplate(): string
    {
        return view('templates.invoice')->render();
    }
}
```

## Email Notification System

### Welcome Email Template

```php
namespace App\Services\Templates;

use CleaniqueCoders\Placeholdify\PlaceholdifyBase;
use CleaniqueCoders\Placeholdify\PlaceholderHandler;

class WelcomeEmailTemplate extends PlaceholdifyBase
{
    protected function configure(): void
    {
        $this->handler
            ->setFallback('')
            ->add('app_name', config('app.name'))
            ->add('app_url', config('app.url'))
            ->add('support_email', config('mail.support_address'))
            ->addDate('current_year', now(), 'Y');
    }

    public function build($user): PlaceholderHandler
    {
        return $this->handler
            ->add('user_name', $user->name)
            ->add('user_email', $user->email)
            ->add('verification_url', $user->getVerificationUrl())
            ->add('login_url', route('login'))
            ->addDate('account_created', $user->created_at, 'F j, Y');
    }
}
```

### Mailable Class

```php
namespace App\Mail;

use App\Models\User;
use App\Services\Templates\WelcomeEmailTemplate;
use Illuminate\Mail\Mailable;

class WelcomeEmail extends Mailable
{
    public function __construct(public User $user)
    {
    }

    public function build()
    {
        $template = new WelcomeEmailTemplate();
        $content = $template->generate($this->user, $this->getTemplate());

        return $this->subject('Welcome to ' . config('app.name'))
                    ->html($content);
    }

    protected function getTemplate(): string
    {
        return '
            <h1>Welcome to {app_name}!</h1>
            <p>Hello {user_name},</p>
            <p>Thank you for joining {app_name}. Your account was created on {account_created}.</p>
            <p>Please verify your email address by clicking the link below:</p>
            <p><a href="{verification_url}">Verify Email Address</a></p>
            <p>If you have any questions, contact us at {support_email}.</p>
            <p>Best regards,<br>The {app_name} Team</p>
            <p><small>&copy; {current_year} {app_name}. All rights reserved.</small></p>
        ';
    }
}
```

## Academic Letter System

### Academic Warning Letter

```php
namespace App\Services\Templates;

use CleaniqueCoders\Placeholdify\PlaceholdifyBase;
use CleaniqueCoders\Placeholdify\PlaceholderHandler;

class AcademicWarningTemplate extends PlaceholdifyBase
{
    protected function configure(): void
    {
        $this->handler->setFallback('N/A');

        $this->handler->registerContext('student', [
            'name' => 'student_name',
            'matric' => 'matric_number',
            'program' => 'program.name',
            'faculty' => 'program.faculty.name',
            'semester' => 'current_semester',
            'gpa' => ['property' => 'gpa', 'formatter' => 'number', 'args' => [2]],
        ]);

        $this->handler->registerContext('university', [
            'name' => 'name',
            'address' => 'address',
            'logo' => fn($uni) => $uni->getFirstMediaUrl('logo'),
        ]);
    }

    public function build($academicRecord): PlaceholderHandler
    {
        return $this->handler
            ->add('letter_ref', $this->generateReferenceNumber($academicRecord))
            ->addDate('letter_date', now(), 'F j, Y')
            ->useContext('student', $academicRecord->student, 'student')
            ->useContext('university', $academicRecord->university, 'university')
            ->add('warning_level', $academicRecord->warning_level)
            ->add('required_gpa', '2.00')
            ->add('improvement_deadline', now()->addMonths(2)->format('F j, Y'))
            ->addLazy('subjects_failed', function() use ($academicRecord) {
                return $academicRecord->failedSubjects->pluck('subject_name')->join(', ');
            });
    }

    protected function generateReferenceNumber($record): string
    {
        return 'AW/' . now()->year . '/' . str_pad($record->id, 5, '0', STR_PAD_LEFT);
    }
}
```

## Certificate Generation

### Course Completion Certificate

```php
namespace App\Services\Templates;

use CleaniqueCoders\Placeholdify\PlaceholdifyBase;
use CleaniqueCoders\Placeholdify\PlaceholderHandler;

class CertificateTemplate extends PlaceholdifyBase
{
    protected function configure(): void
    {
        $this->handler->setFallback('N/A');

        $this->handler->registerFormatter('grade_display', function($grade) {
            return match($grade) {
                'A+', 'A', 'A-' => 'with Distinction',
                'B+', 'B', 'B-' => 'with Merit',
                default => 'satisfactorily'
            };
        });
    }

    public function build($certificate): PlaceholderHandler
    {
        return $this->handler
            ->add('certificate_number', $certificate->certificate_number)
            ->add('recipient_name', strtoupper($certificate->recipient->name))
            ->add('course_title', $certificate->course->title)
            ->add('course_duration', $certificate->course->duration_hours . ' hours')
            ->addDate('completion_date', $certificate->completed_at, 'F j, Y')
            ->addDate('issue_date', now(), 'F j, Y')
            ->addFormatted('grade_text', $certificate->grade, 'grade_display')
            ->add('instructor_name', $certificate->instructor->name)
            ->add('institution_name', $certificate->institution->name)
            ->add('accreditation', $certificate->accreditation_body);
    }
}
```

## Report Generation

### Monthly Sales Report

```php
namespace App\Services\Templates;

use CleaniqueCoders\Placeholdify\PlaceholdifyBase;
use CleaniqueCoders\Placeholdify\PlaceholderHandler;

class SalesReportTemplate extends PlaceholdifyBase
{
    protected function configure(): void
    {
        $this->handler->setFallback('0');

        $this->handler->registerFormatter('percentage', function($value, $decimals = 1) {
            return number_format($value * 100, $decimals) . '%';
        });

        $this->handler->registerFormatter('currency', function($value) {
            return 'RM ' . number_format($value, 2);
        });
    }

    public function build($reportData): PlaceholderHandler
    {
        return $this->handler
            ->add('report_title', 'Monthly Sales Report')
            ->addDate('report_month', $reportData['period'], 'F Y')
            ->addDate('generated_at', now(), 'F j, Y \a\t g:i A')
            ->addFormatted('total_sales', $reportData['total_sales'], 'currency')
            ->addFormatted('previous_month', $reportData['previous_month'], 'currency')
            ->addFormatted('growth_rate', $reportData['growth_rate'], 'percentage')
            ->add('total_orders', number_format($reportData['total_orders']))
            ->add('total_customers', number_format($reportData['total_customers']))
            ->addFormatted('average_order', $reportData['average_order_value'], 'currency')
            ->add('top_product', $reportData['top_selling_product'])
            ->addLazy('sales_breakdown', function() use ($reportData) {
                return $this->generateSalesBreakdown($reportData['breakdown']);
            });
    }

    protected function generateSalesBreakdown($breakdown): string
    {
        return collect($breakdown)->map(function($item) {
            return "{$item['category']}: RM " . number_format($item['amount'], 2);
        })->join("\n");
    }
}
```

## Usage in Controllers

### Document Controller

```php
namespace App\Http\Controllers;

use App\Services\Templates\{
    PermitTemplate,
    InvoiceTemplate,
    CertificateTemplate,
    AcademicWarningTemplate
};

class DocumentController extends Controller
{
    public function generateDocument(Request $request)
    {
        $type = $request->input('type');
        $id = $request->input('id');

        $template = match($type) {
            'permit' => new PermitTemplate(),
            'invoice' => new InvoiceTemplate(),
            'certificate' => new CertificateTemplate(),
            'warning' => new AcademicWarningTemplate(),
            default => throw new InvalidArgumentException("Unknown document type: {$type}")
        };

        $data = $this->loadDocumentData($type, $id);
        $templateContent = $this->getTemplateContent($type);

        $content = $template->generate($data, $templateContent);

        return response($content)
            ->header('Content-Type', 'text/html');
    }

    protected function loadDocumentData(string $type, int $id)
    {
        return match($type) {
            'permit' => ApplianceApplication::with(['student', 'appliance'])->findOrFail($id),
            'invoice' => Invoice::with(['customer', 'items'])->findOrFail($id),
            'certificate' => Certificate::with(['recipient', 'course'])->findOrFail($id),
            'warning' => AcademicRecord::with(['student'])->findOrFail($id),
        };
    }

    protected function getTemplateContent(string $type): string
    {
        return view("templates.{$type}")->render();
    }
}
```

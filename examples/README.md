# PlaceholdifyBase Examples Documentation

This directory contains comprehensive examples of how to use the `PlaceholdifyBase` class to create various types of document generators.

## Available Examples

### 1. OfferLetter.php

**Purpose**: Generate employment offer letters
**Features**:

- Employee and position context mapping
- Salary formatting with currency
- Benefits listing
- Conditional probation period text
- Auto-generated offer reference numbers

**Usage**:

```php
$letter = new OfferLetter();
$content = $letter->generate($offerData, $template);
```

### 2. AcademicWarningLetter.php

**Purpose**: Generate academic warning letters for students
**Features**:

- Student academic context
- GPA formatting
- Dynamic warning levels based on CGPA
- Failed subjects listing
- Conditional final warning text
- Improvement plan generation

**Usage**:

```php
$letter = new AcademicWarningLetter();
$content = $letter->generate($academicRecord, $template);
```

### 3. RentalAgreementLetter.php

**Purpose**: Generate rental/lease agreements
**Features**:

- Custom delimiters `[brackets]` instead of `{braces}`
- Tenant, landlord, and property contexts
- Currency formatting for rent and deposits
- House rules and utilities formatting
- Furnishing details
- Conditional clauses for pets and smoking

**Usage**:

```php
$letter = new RentalAgreementLetter();
$content = $letter->generate($rentalData, $template);
```

### 4. MedicalLeaveLetter.php

**Purpose**: Generate medical leave approval letters
**Features**:

- Employee and medical contexts
- Dynamic leave duration calculation
- Leave type determination
- Compensation details
- Return-to-work conditions
- Benefits continuation information

**Usage**:

```php
$letter = new MedicalLeaveLetter();
$content = $letter->generate($leaveRequest, $template);
```

### 5. EventInvitationLetter.php

**Purpose**: Generate event invitation letters
**Features**:

- Guest and event contexts
- Time formatting
- Special instructions formatting
- Agenda highlights
- VIP treatment conditional text
- Dietary accommodations
- RSVP handling

**Usage**:

```php
$letter = new EventInvitationLetter();
$content = $letter->generate($invitationData, $template);
```

## Key Features Demonstrated

### Context Registration

Each letter class shows how to register reusable contexts:

```php
$this->handler->registerContext('student', [
    'name' => 'student_name',
    'email' => 'email',
    'program' => 'program.name', // Nested relationships
]);
```

### Custom Formatters

Examples of registering and using custom formatters:

```php
$this->handler->registerFormatter('currency', function($value, $currency = 'USD') {
    return $currency . ' ' . number_format($value, 2);
});
```

### Conditional Text

Adding text based on conditions:

```php
$this->handler->addIf(
    $student->gpa >= 3.5,
    'honors',
    'with Honors',
    ''
);
```

### Lazy Evaluation

Deferring expensive operations:

```php
$this->handler->addLazy('expensive_calculation', function() use ($data) {
    return $this->performExpensiveCalculation($data);
});
```

### Custom Delimiters

Using different placeholder delimiters:

```php
$this->handler->setDelimiter('[', ']'); // Use [placeholder] instead of {placeholder}
```

## Running the Examples

### Basic Example

```bash
php examples/comprehensive_letter_examples.php
```

### Individual Testing

```php
require_once 'examples/OfferLetter.php';

$offerData = (object) [
    'candidate' => (object) ['full_name' => 'John Doe'],
    'position' => (object) ['title' => 'Developer'],
    // ... other data
];

$letter = new OfferLetter();
$content = $letter->generate($offerData, $template);
echo $content;
```

## Integration with Laravel

The `LetterGenerationController.php` shows how to integrate these letter classes into a real Laravel application with:

- Database integration
- File storage
- Email notifications
- PDF generation
- Bulk processing
- Preview functionality
- Workflow integration

### Controller Usage Example

```php
public function generateLetter($type, $id)
{
    $letter = match($type) {
        'offer' => new OfferLetter(),
        'warning' => new AcademicWarningLetter(),
        'rental' => new RentalAgreementLetter(),
        // ... other types
    };

    $data = $this->getData($type, $id);
    $template = LetterTemplate::where('type', $type)->first();

    return $letter->generate($data, $template->content);
}
```

## Best Practices Shown

1. **Separation of Concerns**: Each letter type has its own class
2. **Consistent Interface**: All letters extend PlaceholdifyBase
3. **Configuration in Constructor**: Setup formatters and contexts early
4. **Business Logic Encapsulation**: Complex calculations in private methods
5. **Flexible Data Handling**: Support for various data structures
6. **Error Handling**: Graceful fallbacks for missing data
7. **Reference Generation**: Unique identifiers for documents
8. **Template Flexibility**: Support for different placeholder styles

## Template Examples

Each letter class works with templates like:

```text
Dear {employee.name},

We are pleased to offer you the position of {position.title}.
Salary: {salary}
Start Date: {start_date}

{probation_text}

Reference: {offer_reference}
```

The placeholders are automatically replaced with the appropriate values from your data objects.

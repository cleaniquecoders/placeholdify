<?php

/**
 * Complete examples showing how to use various BaseLetter implementations
 */

require_once __DIR__.'/../vendor/autoload.php';

// Include all the letter classes (in a real app, these would be autoloaded)
require_once __DIR__.'/OfferLetter.php';
require_once __DIR__.'/AcademicWarningLetter.php';
require_once __DIR__.'/RentalAgreementLetter.php';
require_once __DIR__.'/MedicalLeaveLetter.php';
require_once __DIR__.'/EventInvitationLetter.php';

use App\Services\Letters\AcademicWarningLetter;
use App\Services\Letters\OfferLetter;
use App\Services\Letters\RentalAgreementLetter;

echo "=== BaseLetter Examples ===\n\n";

// Example 1: Employment Offer Letter
echo "1. EMPLOYMENT OFFER LETTER\n";
echo "==========================\n";

$offerData = (object) [
    'id' => 1,
    'candidate' => (object) [
        'full_name' => 'Sarah Johnson',
        'email' => 'sarah.johnson@email.com',
        'phone_number' => '+60123456789',
        'address' => '123 Main Street, Kuala Lumpur',
    ],
    'position' => (object) [
        'title' => 'Senior Software Engineer',
        'department' => (object) ['name' => 'Engineering'],
        'level' => 'Senior',
    ],
    'annual_salary' => 120000,
    'start_date' => '2024-02-01',
    'benefits' => [
        ['name' => 'Health Insurance', 'description' => 'Full medical and dental coverage'],
        ['name' => 'Annual Leave', 'description' => '25 days per year'],
        ['name' => 'Performance Bonus', 'description' => 'Up to 20% of annual salary'],
    ],
    'probation_period' => 3,
];

$offerTemplate = 'Dear {employee.name},

We are pleased to offer you the position of {position.title} in our {position.department} department.

Position Details:
- Annual Salary: {salary}
- Monthly Salary: {monthly_salary}
- Start Date: {start_date}
- Reference: {offer_reference}

Benefits Package:
{benefits}

{probation_text}

Please respond by {response_deadline}.

Sincerely,
HR Department';

$offerLetter = new OfferLetter;
$content = $offerLetter->generate($offerData, $offerTemplate);
echo $content."\n\n";

// Example 2: Academic Warning Letter
echo "2. ACADEMIC WARNING LETTER\n";
echo "==========================\n";

$academicRecord = (object) [
    'student' => (object) [
        'full_name' => 'Ahmad Rahman',
        'matric_number' => 'A123456',
        'program' => (object) ['name' => 'Computer Science'],
        'current_year' => 2,
        'current_semester' => 4,
        'academic_advisor' => (object) ['name' => 'Dr. Lee Wei Ming'],
    ],
    'cgpa' => 1.85,
    'semester_start' => '2024-01-15',
    'semester_end' => '2024-05-15',
    'failed_subjects' => [
        (object) ['code' => 'CS201', 'name' => 'Data Structures', 'grade' => 'F'],
        (object) ['code' => 'CS202', 'name' => 'Algorithms', 'grade' => 'F'],
    ],
    'is_final_warning' => false,
];

$warningTemplate = 'ACADEMIC WARNING NOTICE

Date: {letter_date}
Reference: {letter_reference}

Dear {student.name} (Matric: {student.matric}),

This is a {warning_level} regarding your academic performance in {student.program}.

Current Academic Standing:
- Current CGPA: {current_cgpa}
- Required CGPA: {required_cgpa}
- Semester: {student.semester} of Year {student.year}

Failed Subjects:
{failed_subjects}

{warning_description}

Required Actions:
{action_required}

Improvement Plan:
{improvement_plan}

{final_warning_text}

Academic Advisor: {student.advisor}

Academic Affairs Office';

$warningLetter = new AcademicWarningLetter;
$content = $warningLetter->generate($academicRecord, $warningTemplate);
echo $content."\n\n";

// Example 3: Rental Agreement (with different delimiters)
echo "3. RENTAL AGREEMENT LETTER\n";
echo "==========================\n";

$rentalData = (object) [
    'id' => 1,
    'tenant' => (object) [
        'full_name' => 'Lisa Chen',
        'identity_number' => '920315-14-5678',
        'phone_number' => '+60198765432',
        'email' => 'lisa.chen@email.com',
        'occupation' => 'Marketing Manager',
    ],
    'landlord' => (object) [
        'full_name' => 'Mr. Robert Tan',
        'identity_number' => '750820-08-1234',
        'phone_number' => '+60123456789',
        'address' => '456 Landlord Street, Petaling Jaya',
    ],
    'property' => (object) [
        'full_address' => '789 Rental Avenue, Subang Jaya, Selangor',
        'property_type' => 'Condominium',
        'size_sqft' => 1200,
        'bedroom_count' => 3,
        'bathroom_count' => 2,
    ],
    'monthly_rent' => 2500.00,
    'security_deposit' => 5000.00,
    'utility_deposit' => 500.00,
    'lease_start_date' => '2024-02-01',
    'lease_end_date' => '2025-01-31',
    'rent_due_day' => 1,
    'included_utilities' => ['Water', 'Internet'],
    'house_rules' => [
        'No smoking inside the unit',
        'Quiet hours from 10 PM to 7 AM',
        'Maximum 2 visitors at a time',
    ],
    'furnishing' => [
        ['room' => 'living room', 'item' => 'Sofa set'],
        ['room' => 'living room', 'item' => 'TV stand'],
        ['room' => 'kitchen', 'item' => 'Refrigerator'],
        ['room' => 'kitchen', 'item' => 'Washing machine'],
    ],
    'pets_allowed' => false,
    'smoking_allowed' => false,
];

$rentalTemplate = 'RENTAL AGREEMENT

Agreement Date: [agreement_date]
Reference: [agreement_reference]

LANDLORD: [landlord.name] (IC: [landlord.ic])
TENANT: [tenant.name] (IC: [tenant.ic])

PROPERTY DETAILS:
Address: [property.address]
Type: [property.type] ([property.size] sq ft)
Bedrooms: [property.rooms] | Bathrooms: [property.bathrooms]

RENTAL TERMS:
Monthly Rent: [monthly_rent]
Security Deposit: [security_deposit]
Utility Deposit: [utility_deposit]
Lease Period: [lease_duration] ([lease_start] to [lease_end])
Rent Due: [rent_due_date] of each month

INCLUDED UTILITIES:
[included_utilities]

FURNISHING:
[furnishing_details]

HOUSE RULES:
[house_rules]

SPECIAL CLAUSES:
[pet_clause]
[smoking_clause]

Landlord: [landlord.name]
Tenant: [tenant.name]';

$rentalLetter = new RentalAgreementLetter;
$content = $rentalLetter->generate($rentalData, $rentalTemplate);
echo $content."\n\n";

echo "=== All BaseLetter examples completed! ===\n";
echo "\nKey Features Demonstrated:\n";
echo "• Different delimiter styles ([brackets] vs {braces})\n";
echo "• Context registration and reuse\n";
echo "• Custom formatters (currency, time, GPA)\n";
echo "• Conditional placeholders\n";
echo "• Lazy evaluation\n";
echo "• Complex data processing\n";
echo "• Reference number generation\n";
echo "• Nested object access\n";

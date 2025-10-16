<?php

use Illuminate\Support\Facades\File;

beforeEach(function () {
    // Clean up test files before each test
    $testPaths = [
        'app/Services/Placeholders/Templates',
        'app/Services/Placeholders/Contexts',
        'app/Services/Placeholders/Formatters',
    ];

    foreach ($testPaths as $path) {
        if (File::exists(base_path($path))) {
            File::deleteDirectory(base_path($path));
        }
    }
});

afterEach(function () {
    // Clean up test files after each test
    $testPaths = [
        'app/Services/Placeholders/Templates',
        'app/Services/Placeholders/Contexts',
        'app/Services/Placeholders/Formatters',
    ];

    foreach ($testPaths as $path) {
        if (File::exists(base_path($path))) {
            File::deleteDirectory(base_path($path));
        }
    }
});

it('displays available component types with list option', function () {
    $this->artisan('make:placeholder', ['name' => 'Test', 'type' => 'template', '--list' => true])
        ->expectsOutput('Available Component Types:')
        ->expectsOutput('=========================')
        ->expectsOutput('• template - Create a template class for content placeholders')
        ->expectsOutput('• context  - Create a context class for object mappings')
        ->expectsOutput('• formatter - Create a formatter class for data transformation')
        ->assertSuccessful();
});

it('creates a template class successfully', function () {
    $this->artisan('make:placeholder', ['name' => 'InvoiceTemplate', 'type' => 'template'])
        ->expectsOutput('✅ InvoiceTemplate template created successfully!')
        ->assertSuccessful();

    $filePath = base_path('app/Services/Placeholders/Templates/InvoiceTemplate.php');
    expect(File::exists($filePath))->toBeTrue();

    $content = File::get($filePath);
    expect($content)->toContain('namespace App\Services\Placeholders\Templates;')
        ->toContain('class InvoiceTemplate extends PlaceholdifyBase')
        ->toContain('use CleaniqueCoders\Placeholdify\PlaceholderHandler;')
        ->toContain('use CleaniqueCoders\Placeholdify\PlaceholdifyBase;');
});

it('creates a context class successfully', function () {
    $this->artisan('make:placeholder', ['name' => 'UserContext', 'type' => 'context'])
        ->expectsOutput('✅ UserContext context created successfully!')
        ->assertSuccessful();

    $filePath = base_path('app/Services/Placeholders/Contexts/UserContext.php');
    expect(File::exists($filePath))->toBeTrue();

    $content = File::get($filePath);
    expect($content)->toContain('namespace App\Services\Placeholders\Contexts;')
        ->toContain('class UserContext implements ContextInterface')
        ->toContain('use CleaniqueCoders\Placeholdify\Contracts\ContextInterface;')
        ->toContain("return 'user';");
});

it('creates a formatter class successfully', function () {
    $this->artisan('make:placeholder', ['name' => 'PhoneFormatter', 'type' => 'formatter'])
        ->expectsOutput('✅ PhoneFormatter formatter created successfully!')
        ->assertSuccessful();

    $filePath = base_path('app/Services/Placeholders/Formatters/PhoneFormatter.php');
    expect(File::exists($filePath))->toBeTrue();

    $content = File::get($filePath);
    expect($content)->toContain('namespace App\Services\Placeholders\Formatters;')
        ->toContain('class PhoneFormatter implements FormatterInterface')
        ->toContain('use CleaniqueCoders\Placeholdify\Contracts\FormatterInterface;')
        ->toContain("return 'phone';");
});

it('fails with invalid component type', function () {
    $this->artisan('make:placeholder', ['name' => 'TestClass', 'type' => 'invalid'])
        ->expectsOutput("Invalid component type 'invalid'. Valid types: template, context, formatter")
        ->assertFailed();
});

it('fails when file already exists', function () {
    // Create the file first
    $filePath = base_path('app/Services/Placeholders/Templates/ExistingTemplate.php');
    File::ensureDirectoryExists(dirname($filePath));
    File::put($filePath, '<?php // existing file');

    $this->artisan('make:placeholder', ['name' => 'ExistingTemplate', 'type' => 'template'])
        ->expectsOutput("Component ExistingTemplate already exists at {$filePath}")
        ->assertFailed();
});

it('creates directory structure if it does not exist', function () {
    $basePath = base_path('app/Services/Placeholders/Templates');
    expect(File::exists($basePath))->toBeFalse();

    $this->artisan('make:placeholder', ['name' => 'NewTemplate', 'type' => 'template'])
        ->assertSuccessful();

    expect(File::exists($basePath))->toBeTrue();
    expect(File::exists($basePath.'/NewTemplate.php'))->toBeTrue();
});

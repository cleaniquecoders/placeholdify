<?php

require_once 'vendor/autoload.php';

use CleaniqueCoders\Placeholdify\Commands\PlaceholdifyCommand;
use Illuminate\Console\Application;

// Create a mock Laravel application context
$app = new class
{
    public function runningInConsole()
    {
        return true;
    }

    public function configPath()
    {
        return __DIR__.'/config';
    }

    public function basePath($path = '')
    {
        return __DIR__.($path ? '/'.$path : '');
    }
};

// Mock config function
function config($key, $default = null)
{
    if ($key === 'placeholdify.template_path') {
        return 'app/Services/Placeholders';
    }

    return $default;
}

// Mock base_path function
function base_path($path = '')
{
    return __DIR__.($path ? '/'.$path : '');
}

// Mock now function
function now()
{
    return new DateTime;
}

echo "Testing PlaceholdifyCommand structure...\n";

// Test command signature parsing
$command = new PlaceholdifyCommand;
$reflection = new ReflectionClass($command);
$signatureProperty = $reflection->getProperty('signature');
$signatureProperty->setAccessible(true);
echo 'Command signature: '.$signatureProperty->getValue($command)."\n";

$descriptionProperty = $reflection->getProperty('description');
$descriptionProperty->setAccessible(true);
echo 'Command description: '.$descriptionProperty->getValue($command)."\n";

echo "\nCommand structure looks good!\n";

// Cleanup test files
unlink('test_example_template.php');
unlink('test_example_context.php');
unlink('test_example_formatter.php');

echo "Test completed successfully.\n";

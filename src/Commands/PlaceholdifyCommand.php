<?php

namespace CleaniqueCoders\Placeholdify\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class PlaceholdifyCommand extends Command
{
    public $signature = 'placeholdify:make-template {name} {--type=basic : Template type} {--path=app/Services/Templates : Path where template will be created} {--list : List available template types}';

    public $description = 'Create a new Placeholdify template class';

    public function handle(): int
    {
        // Show available template types
        if ($this->option('list')) {
            return $this->listTemplateTypes();
        }

        $name = $this->argument('name');
        $type = $this->option('type') ?? 'basic';
        $path = $this->option('path');

        // Get available stubs
        $availableStubs = $this->getAvailableStubs();

        if (empty($availableStubs)) {
            $this->error('No template stubs found. Please run: php artisan vendor:publish --tag=placeholdify-stubs');

            return self::FAILURE;
        }

        // Validate template type
        if (! in_array($type, $availableStubs)) {
            $this->error("Invalid template type '{$type}'. Available types: ".implode(', ', $availableStubs));
            $this->line('Use --list to see all available template types.');

            return self::FAILURE;
        }

        $className = Str::studly($name);
        $fileName = $className.'.php';
        $namespace = $this->generateNamespace($path);

        // Create directory if it doesn't exist
        $fullPath = base_path($path);
        if (! is_dir($fullPath)) {
            mkdir($fullPath, 0755, true);
        }

        $filePath = $fullPath.'/'.$fileName;

        // Check if file already exists
        if (file_exists($filePath)) {
            $this->error("Template class {$className} already exists at {$filePath}");

            return self::FAILURE;
        }

        // Load and process stub
        $stubContent = $this->loadStub($type);
        if ($stubContent === null) {
            $this->error("Could not load stub for type '{$type}'");

            return self::FAILURE;
        }

        $content = $this->processStub($stubContent, $className, $namespace);

        // Write file
        file_put_contents($filePath, $content);

        $this->info("✅ Template '{$className}' created successfully!");
        $this->line("📁 Location: {$filePath}");
        $this->line("📝 Namespace: {$namespace}");
        $this->line("🎯 Type: {$type}");

        return self::SUCCESS;
    }

    protected function listTemplateTypes(): int
    {
        $availableStubs = $this->getAvailableStubs();

        if (empty($availableStubs)) {
            $this->warn('No template stubs found.');
            $this->line('Run: php artisan vendor:publish --tag=placeholdify-stubs');

            return self::SUCCESS;
        }

        $this->info('Available Template Types:');
        $this->line('========================');

        foreach ($availableStubs as $type) {
            $this->line("• {$type}");
        }

        $this->newLine();
        $this->line('Usage: php artisan placeholdify:make-template YourTemplate --type=basic');

        return self::SUCCESS;
    }

    protected function getAvailableStubs(): array
    {
        $stubPaths = [
            base_path('stubs/placeholdify'), // Published stubs
            __DIR__.'/../../stubs/placeholdify', // Package stubs
        ];

        $stubs = [];

        foreach ($stubPaths as $stubPath) {
            if (! is_dir($stubPath)) {
                continue;
            }

            $files = glob($stubPath.'/template.*.stub');

            foreach ($files as $file) {
                $filename = basename($file);
                if (preg_match('/^template\.(.+)\.stub$/', $filename, $matches)) {
                    $stubs[] = $matches[1];
                }
            }
        }

        return array_unique($stubs);
    }

    protected function loadStub(string $type): ?string
    {
        $stubPaths = [
            base_path("stubs/placeholdify/template.{$type}.stub"), // Published stubs first
            __DIR__."/../../stubs/placeholdify/template.{$type}.stub", // Package stubs as fallback
        ];

        foreach ($stubPaths as $stubPath) {
            if (file_exists($stubPath)) {
                return file_get_contents($stubPath);
            }
        }

        return null;
    }

    protected function processStub(string $content, string $className, string $namespace): string
    {
        return str_replace(
            ['{{ namespace }}', '{{ class }}'],
            [$namespace, $className],
            $content
        );
    }

    protected function generateNamespace(string $path): string
    {
        $normalizedPath = str_replace(['/', '\\'], '\\', $path);
        $namespace = str_replace('app\\', 'App\\', Str::studly($normalizedPath));

        return $namespace;
    }
}

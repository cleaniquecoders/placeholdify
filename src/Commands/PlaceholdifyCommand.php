<?php

namespace CleaniqueCoders\Placeholdify\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class PlaceholdifyCommand extends Command
{
    public $signature = 'make:placeholder {name} {type : Component type (template, context, formatter)} {--list : List available component types}';

    public $description = 'Create a new Placeholdify component (template, context, or formatter)';

    public function handle(): int
    {
        // Show available component types
        if ($this->option('list')) {
            return $this->listComponentTypes();
        }

        $name = $this->argument('name');
        $type = strtolower($this->argument('type'));

        // Validate component type
        $validTypes = ['template', 'context', 'formatter'];
        if (! in_array($type, $validTypes)) {
            $this->error("Invalid component type '{$type}'. Valid types: ".implode(', ', $validTypes));
            $this->line('Use --list to see all available component types.');

            return self::FAILURE;
        }

        // Get base path from config
        $basePath = config('placeholdify.template_path', 'app/Services/Placeholders');

        $className = Str::studly($name);
        $fileName = $className.'.php';

        // Determine subdirectory and namespace based on type
        $subPath = match ($type) {
            'template' => 'Templates',
            'context' => 'Contexts',
            'formatter' => 'Formatters',
        };

        $fullPath = base_path($basePath.'/'.$subPath);
        $namespace = $this->generateNamespace($basePath.'/'.$subPath);

        // Create directory if it doesn't exist
        if (! is_dir($fullPath)) {
            mkdir($fullPath, 0755, true);
        }

        $filePath = $fullPath.'/'.$fileName;

        // Check if file already exists
        if (file_exists($filePath)) {
            $this->error("Component {$className} already exists at {$filePath}");

            return self::FAILURE;
        }

        // Generate content based on type
        $content = match ($type) {
            'template' => $this->generateTemplateContent($className, $namespace),
            'context' => $this->generateContextContent($className, $namespace),
            'formatter' => $this->generateFormatterContent($className, $namespace),
        };

        // Write file
        file_put_contents($filePath, $content);

        $this->info("✅ {$className} {$type} created successfully!");
        $this->line("📁 Location: {$filePath}");
        $this->line("📝 Namespace: {$namespace}");
        $this->line("🎯 Type: {$type}");

        return self::SUCCESS;
    }

    protected function listComponentTypes(): int
    {
        $this->info('Available Component Types:');
        $this->line('=========================');

        $this->line('• <comment>template</comment> - Create a template class for content placeholders');
        $this->line('• <comment>context</comment>  - Create a context class for object mappings');
        $this->line('• <comment>formatter</comment> - Create a formatter class for data transformation');

        $this->newLine();
        $this->line('Usage Examples:');
        $this->line('  php artisan make:placeholder InvoiceTemplate template');
        $this->line('  php artisan make:placeholder UserContext context');
        $this->line('  php artisan make:placeholder PhoneFormatter formatter');

        return self::SUCCESS;
    }

    protected function generateTemplateContent(string $className, string $namespace): string
    {
        return "<?php

namespace {$namespace};

use CleaniqueCoders\Placeholdify\PlaceholderHandler;
use CleaniqueCoders\Placeholdify\PlaceholdifyBase;

/**
 * {$className} Template
 *
 * Template for handling placeholders in content.
 */
class {$className} extends PlaceholdifyBase
{
    protected function configure(): void
    {
        \$this->handler->setFallback('N/A');

        // Add any custom configuration here
        // Example: \$this->handler->registerFormatter('custom', function(\$value) { return strtoupper(\$value); });
    }

    public function build(mixed \$data): PlaceholderHandler
    {
        return \$this->handler
            ->add('title', \$data->title ?? 'Untitled')
            ->addDate('created_at', now(), 'F j, Y')
            ->add('content', \$data->content ?? '');
            // Add more placeholders as needed
    }
}
";
    }

    protected function generateContextContent(string $className, string $namespace): string
    {
        $contextName = Str::snake(str_replace('Context', '', $className));

        return "<?php

namespace {$namespace};

use CleaniqueCoders\Placeholdify\Contracts\ContextInterface;

/**
 * {$className} Context
 *
 * Context class for {$contextName} object mappings.
 */
class {$className} implements ContextInterface
{
    public function getName(): string
    {
        return '{$contextName}';
    }

    public function getMapping(): array
    {
        return [
            'id' => 'id',
            'name' => 'name',
            'created_at' => fn(\$object) => \$object->created_at?->format('F j, Y') ?? 'Unknown',
            // Add more mappings as needed
        ];
    }

    public function canProcess(mixed \$object): bool
    {
        // Implement your validation logic here
        return is_object(\$object) && property_exists(\$object, 'id');
    }

    public function getSupportedTypes(): array
    {
        return [
            // Add supported class names or interfaces
            // Example: User::class, 'App\\Models\\User'
        ];
    }
}
";
    }

    protected function generateFormatterContent(string $className, string $namespace): string
    {
        $formatterName = Str::snake(str_replace('Formatter', '', $className));

        return "<?php

namespace {$namespace};

use CleaniqueCoders\Placeholdify\Contracts\FormatterInterface;

/**
 * {$className} Formatter
 *
 * Custom formatter for {$formatterName} data transformation.
 */
class {$className} implements FormatterInterface
{
    public function getName(): string
    {
        return '{$formatterName}';
    }

    public function canFormat(mixed \$value): bool
    {
        // Implement your validation logic here
        return is_string(\$value) || is_numeric(\$value);
    }

    public function format(mixed \$value, mixed ...\$options): string
    {
        if (empty(\$value)) {
            return 'N/A';
        }

        // Implement your formatting logic here
        // You can use \$options for additional parameters

        return (string) \$value;
    }
}
";
    }

    protected function generateNamespace(string $path): string
    {
        $normalizedPath = str_replace(['/', '\\'], '\\', $path);
        $namespace = str_replace('app\\', 'App\\', Str::studly($normalizedPath));

        return $namespace;
    }
}

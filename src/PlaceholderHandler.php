<?php

namespace CleaniqueCoders\Placeholdify;

use CleaniqueCoders\Placeholdify\Contracts\ContextInterface;
use CleaniqueCoders\Placeholdify\Contracts\FormatterInterface;
use CleaniqueCoders\Placeholdify\Formatters\CurrencyFormatter;
use CleaniqueCoders\Placeholdify\Formatters\DateFormatter;
use CleaniqueCoders\Placeholdify\Formatters\LowerFormatter;
use CleaniqueCoders\Placeholdify\Formatters\NumberFormatter;
use CleaniqueCoders\Placeholdify\Formatters\TitleFormatter;
use CleaniqueCoders\Placeholdify\Formatters\UpperFormatter;
use Closure;

class PlaceholderHandler
{
    protected array $placeholders = [];

    protected array $formatters = [];

    protected array $contexts = [];

    protected string $fallback = 'N/A';

    protected string $startDelimiter = '{';

    protected string $endDelimiter = '}';

    public function __construct()
    {
        $this->loadConfig();
        $this->registerBuiltInFormatters();
    }

    /**
     * Load configuration from config file
     */
    protected function loadConfig(): void
    {
        if (function_exists('config') && function_exists('app') && app()->bound('config')) {
            try {
                $config = config('placeholdify', []);

                $this->fallback = $config['fallback'] ?? 'N/A';

                if (isset($config['delimiter'])) {
                    $this->startDelimiter = $config['delimiter']['start'] ?? '{';
                    $this->endDelimiter = $config['delimiter']['end'] ?? '}';
                }

                // Register custom formatter classes from config
                if (isset($config['formatters']) && is_array($config['formatters'])) {
                    foreach ($config['formatters'] as $name => $formatterClass) {
                        if (is_string($formatterClass) && class_exists($formatterClass)) {
                            $formatterInstance = new $formatterClass;
                            if ($formatterInstance instanceof FormatterInterface) {
                                $this->registerFormatter($formatterInstance);
                            }
                        }
                    }
                }

                // Register context classes from config only
                if (isset($config['context_classes']) && is_array($config['context_classes'])) {
                    foreach ($config['context_classes'] as $contextClass) {
                        if (is_string($contextClass) && class_exists($contextClass)) {
                            $contextInstance = new $contextClass;
                            if ($contextInstance instanceof ContextInterface) {
                                $this->registerContext($contextInstance);
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                // If config loading fails, use defaults
                $this->fallback = 'N/A';
                $this->startDelimiter = '{';
                $this->endDelimiter = '}';
            }
        }
    }

    /**
     * Register built-in formatters based on configuration
     */
    protected function registerBuiltInFormatters(): void
    {
        $builtInFormatters = [];

        if (function_exists('config') && function_exists('app') && app()->bound('config')) {
            try {
                $builtInFormatters = config('placeholdify.built_in_formatters', [
                    'date' => true,
                    'currency' => true,
                    'number' => true,
                    'upper' => true,
                    'lower' => true,
                    'title' => true,
                ]);
            } catch (\Exception $e) {
                // Use defaults if config fails
                $builtInFormatters = [
                    'date' => true,
                    'currency' => true,
                    'number' => true,
                    'upper' => true,
                    'lower' => true,
                    'title' => true,
                ];
            }
        } else {
            // Default configuration when not in Laravel environment
            $builtInFormatters = [
                'date' => true,
                'currency' => true,
                'number' => true,
                'upper' => true,
                'lower' => true,
                'title' => true,
            ];
        }

        // Register enabled built-in formatters
        if ($builtInFormatters['date'] ?? true) {
            $this->registerFormatter(new DateFormatter);
        }

        if ($builtInFormatters['currency'] ?? true) {
            $this->registerFormatter(new CurrencyFormatter);
        }

        if ($builtInFormatters['number'] ?? true) {
            $this->registerFormatter(new NumberFormatter);
        }

        if ($builtInFormatters['upper'] ?? true) {
            $this->registerFormatter(new UpperFormatter);
        }

        if ($builtInFormatters['lower'] ?? true) {
            $this->registerFormatter(new LowerFormatter);
        }

        if ($builtInFormatters['title'] ?? true) {
            $this->registerFormatter(new TitleFormatter);
        }
    }

    /**
     * Add a single placeholder
     */
    public function add(string $key, mixed $value, mixed $fallback = null): self
    {
        $this->placeholders[$key] = $value ?? $fallback ?? $this->fallback;

        return $this;
    }

    /**
     * Add multiple placeholders
     */
    public function addMany(array $placeholders): self
    {
        foreach ($placeholders as $key => $value) {
            $this->add($key, $value);
        }

        return $this;
    }

    /**
     * Add formatted date placeholder
     */
    public function addDate(string $key, mixed $date, string $format = 'Y-m-d', mixed $fallback = null): self
    {
        if ($date === null) {
            $this->add($key, $fallback ?? $this->fallback);

            return $this;
        }

        try {
            $formatted = $this->formatters['date']($date, $format);
            $this->add($key, $formatted);
        } catch (\Exception $e) {
            $this->add($key, $fallback ?? $this->fallback);
        }

        return $this;
    }

    /**
     * Add nullable placeholder with multiple fallback values
     */
    public function addNullable(string $key, mixed ...$values): self
    {
        foreach ($values as $value) {
            if ($value !== null && $value !== '') {
                $this->add($key, $value);

                return $this;
            }
        }

        $this->add($key, $this->fallback);

        return $this;
    }

    /**
     * Add formatted placeholder
     */
    public function addFormatted(string $key, mixed $value, string $formatter, mixed ...$args): self
    {
        if (! isset($this->formatters[$formatter])) {
            $this->add($key, $value);

            return $this;
        }

        try {
            $formatted = $this->formatters[$formatter]($value, ...$args);
            $this->add($key, $formatted);
        } catch (\Exception $e) {
            $this->add($key, $this->fallback);
        }

        return $this;
    }

    /**
     * Add lazy-evaluated placeholder
     */
    public function addLazy(string $key, Closure $callback, mixed $fallback = null): self
    {
        try {
            $value = $callback();
            $this->add($key, $value, $fallback);
        } catch (\Exception $e) {
            $this->add($key, $fallback ?? $this->fallback);
        }

        return $this;
    }

    /**
     * Add conditional placeholder
     */
    public function addIf(bool $condition, string $key, mixed $value, mixed $elseValue = null): self
    {
        $this->add($key, $condition ? $value : ($elseValue ?? $this->fallback));

        return $this;
    }

    /**
     * Add placeholders from object context
     */
    public function addFromContext(string $prefix, object $object, array $mapping): self
    {
        foreach ($mapping as $key => $config) {
            $placeholderKey = $prefix ? "{$prefix}.{$key}" : $key;

            if (is_array($config)) {
                $property = $config['property'] ?? $key;
                $value = $this->getObjectValue($object, $property);

                if (isset($config['formatter'])) {
                    $this->addFormatted($placeholderKey, $value, $config['formatter']);
                } else {
                    $this->add($placeholderKey, $value);
                }
            } elseif ($config instanceof Closure) {
                $this->addLazy($placeholderKey, fn () => $config($object));
            } else {
                $value = $this->getObjectValue($object, $config);
                $this->add($placeholderKey, $value);
            }
        }

        return $this;
    }

    /**
     * Use context with an object (supports both array-based and contract-based contexts)
     */
    public function useContext(string $name, object $object, string $prefix = ''): self
    {
        if (isset($this->contexts[$name])) {
            $context = $this->contexts[$name];

            if ($context instanceof ContextInterface) {
                // Handle ContextInterface instances
                if (! $context->canProcess($object)) {
                    return $this;
                }

                return $this->addFromContext($prefix, $object, $context->getMapping());
            } else {
                // Handle array-based contexts (legacy support)
                return $this->addFromContext($prefix, $object, $context);
            }
        }

        return $this;
    }

    /**
     * Register context mapping with name and array
     */
    public function registerContextMapping(string $name, array $mapping): self
    {
        $this->contexts[$name] = $mapping;

        return $this;
    }

    /**
     * Register context instance
     */
    public function registerContext(ContextInterface $context): self
    {
        $this->contexts[$context->getName()] = $context;

        return $this;
    }

    /**
     * Get all registered context names
     */
    public function getRegisteredContexts(): array
    {
        return array_keys($this->contexts);
    }

    /**
     * Get all registered context instances
     */
    public function getRegisteredContextInstances(): array
    {
        $instances = [];
        foreach ($this->contexts as $name => $context) {
            if ($context instanceof ContextInterface) {
                $instances[] = $name;
            }
        }

        return $instances;
    }

    /**
     * Check if a context is registered
     */
    public function hasContext(string $name): bool
    {
        return isset($this->contexts[$name]);
    }

    /**
     * Register custom formatter instance
     */
    public function registerFormatter(FormatterInterface $formatter): self
    {
        $this->formatters[$formatter->getName()] = function ($value, ...$args) use ($formatter) {
            if (! $formatter->canFormat($value)) {
                return $this->fallback;
            }

            return $formatter->format($value, ...$args);
        };

        return $this;
    }

    /**
     * Check if a formatter is registered
     */
    public function hasFormatter(string $name): bool
    {
        return isset($this->formatters[$name]);
    }

    /**
     * Get all registered formatter names
     */
    public function getRegisteredFormatters(): array
    {
        return array_keys($this->formatters);
    }

    /**
     * Unregister a formatter
     */
    public function unregisterFormatter(string $name): self
    {
        unset($this->formatters[$name]);

        return $this;
    }

    /**
     * Set custom delimiters
     */
    public function setDelimiter(string $start, ?string $end = null): self
    {
        $this->startDelimiter = $start;
        $this->endDelimiter = $end ?? $start;

        return $this;
    }

    /**
     * Set default fallback value
     */
    public function setFallback(mixed $value): self
    {
        $this->fallback = $value;

        return $this;
    }

    /**
     * Replace placeholders in content
     */
    public function replace(string $content): string
    {
        // First, replace known placeholders
        foreach ($this->placeholders as $key => $value) {
            $placeholder = $this->startDelimiter.$key.$this->endDelimiter;
            $content = str_replace($placeholder, (string) $value, $content);
        }

        // Then, replace any remaining unknown placeholders with fallback
        $pattern = '/'.preg_quote($this->startDelimiter).'([^'.preg_quote($this->endDelimiter).']+)'.preg_quote($this->endDelimiter).'/';
        $content = preg_replace($pattern, $this->fallback, $content);

        return $content;
    }

    /**
     * Replace placeholders with modifier support
     */
    public function replaceWithModifiers(string $content): string
    {
        $pattern = '/'.preg_quote($this->startDelimiter).'([^'.preg_quote($this->endDelimiter).']+)'.preg_quote($this->endDelimiter).'/';

        return preg_replace_callback($pattern, function ($matches) {
            $placeholder = $matches[1];

            if (strpos($placeholder, '|') !== false) {
                [$key, $modifierString] = explode('|', $placeholder, 2);
                $key = trim($key);

                if (! isset($this->placeholders[$key])) {
                    return $this->fallback;
                }

                $value = $this->placeholders[$key];
                $modifiers = explode('|', $modifierString);

                foreach ($modifiers as $modifier) {
                    $modifier = trim($modifier);

                    if (strpos($modifier, ':') !== false) {
                        [$formatterName, $args] = explode(':', $modifier, 2);
                        $args = array_map('trim', explode(',', $args));
                    } else {
                        $formatterName = $modifier;
                        $args = [];
                    }

                    if (isset($this->formatters[$formatterName])) {
                        try {
                            $value = $this->formatters[$formatterName]($value, ...$args);
                        } catch (\Exception $e) {
                            // Continue with original value if formatting fails
                        }
                    }
                }

                return $value;
            }

            return $this->placeholders[$placeholder] ?? $this->fallback;
        }, $content);
    }

    /**
     * Get all placeholders
     */
    public function all(): array
    {
        return $this->placeholders;
    }

    /**
     * Clear all placeholders
     */
    public function clear(): self
    {
        $this->placeholders = [];

        return $this;
    }

    /**
     * Static method for quick processing
     */
    public static function process(string $content, array $placeholders, string $delimiter = '{}'): string
    {
        $handler = new static;

        if (strlen($delimiter) === 2) {
            $handler->setDelimiter($delimiter[0], $delimiter[1]);
        }

        return $handler->addMany($placeholders)->replace($content);
    }

    /**
     * Register global formatter (only supports class names)
     */
    public static function registerGlobalFormatter(string $name, string $formatterClass): void
    {
        if (function_exists('config')) {
            $formatters = config('placeholdify.formatters', []);
            $formatters[$name] = $formatterClass;
            config(['placeholdify.formatters' => $formatters]);
        }
    }

    /**
     * Get value from object using dot notation
     */
    protected function getObjectValue(object $object, string $property): mixed
    {
        if (strpos($property, '.') === false) {
            return $object->{$property} ?? null;
        }

        $parts = explode('.', $property);
        $value = $object;

        foreach ($parts as $part) {
            if (is_object($value) && isset($value->{$part})) {
                $value = $value->{$part};
            } else {
                return null;
            }
        }

        return $value;
    }
}

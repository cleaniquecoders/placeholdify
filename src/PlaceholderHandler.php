<?php

namespace CleaniqueCoders\Placeholdify;

use Closure;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

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
        if (function_exists('config')) {
            $config = config('placeholdify', []);

            $this->fallback = $config['fallback'] ?? 'N/A';

            if (isset($config['delimiter'])) {
                $this->startDelimiter = $config['delimiter']['start'] ?? '{';
                $this->endDelimiter = $config['delimiter']['end'] ?? '}';
            }

            if (isset($config['formatters']) && is_array($config['formatters'])) {
                foreach ($config['formatters'] as $name => $formatter) {
                    $this->registerFormatter($name, $formatter);
                }
            }

            if (isset($config['contexts']) && is_array($config['contexts'])) {
                foreach ($config['contexts'] as $name => $context) {
                    $this->registerContext($name, $context);
                }
            }
        }
    }

    /**
     * Register built-in formatters
     */
    protected function registerBuiltInFormatters(): void
    {
        $this->registerFormatter('date', function ($value, $format = 'Y-m-d') {
            if ($value instanceof Carbon) {
                return $value->format($format);
            }

            return Carbon::parse($value)->format($format);
        });

        $this->registerFormatter('currency', function ($value, $currency = 'USD') {
            return $currency.' '.number_format((float) $value, 2);
        });

        $this->registerFormatter('number', function ($value, $decimals = 0) {
            return number_format((float) $value, $decimals);
        });

        $this->registerFormatter('upper', function ($value) {
            return strtoupper($value);
        });

        $this->registerFormatter('lower', function ($value) {
            return strtolower($value);
        });

        $this->registerFormatter('title', function ($value) {
            return Str::title($value);
        });
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
     * Use registered context
     */
    public function useContext(string $name, object $object, string $prefix = ''): self
    {
        if (! isset($this->contexts[$name])) {
            return $this;
        }

        return $this->addFromContext($prefix, $object, $this->contexts[$name]);
    }

    /**
     * Register context mapping
     */
    public function registerContext(string $name, array $mapping): self
    {
        $this->contexts[$name] = $mapping;

        return $this;
    }

    /**
     * Register custom formatter
     */
    public function registerFormatter(string $name, Closure $formatter): self
    {
        $this->formatters[$name] = $formatter;

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
     * Register global context
     */
    public static function registerGlobalContext(string $name, array $mapping): void
    {
        if (function_exists('config')) {
            $contexts = config('placeholdify.contexts', []);
            $contexts[$name] = $mapping;
            config(['placeholdify.contexts' => $contexts]);
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

<?php

use CleaniqueCoders\Placeholdify\Contracts\FormatterInterface;
use CleaniqueCoders\Placeholdify\Formatters\CurrencyFormatter;
use CleaniqueCoders\Placeholdify\Formatters\DateFormatter;
use CleaniqueCoders\Placeholdify\Formatters\SlugFormatter;
use CleaniqueCoders\Placeholdify\Formatters\UpperFormatter;
use CleaniqueCoders\Placeholdify\PlaceholderHandler;

it('can register and use formatter instances', function () {
    $handler = new PlaceholderHandler;

    // Test that built-in formatters are registered and working
    $result = $handler
        ->addFormatted('name', 'john doe', 'upper')
        ->replace('Name: {name}');

    expect($result)->toBe('Name: JOHN DOE');
});

it('can register custom formatter instance', function () {
    $handler = new PlaceholderHandler;

    // Create a custom formatter
    $customFormatter = new class implements FormatterInterface
    {
        public function format(mixed $value, mixed ...$args): string
        {
            return 'Custom: '.strtoupper($value);
        }

        public function getName(): string
        {
            return 'custom';
        }

        public function canFormat(mixed $value): bool
        {
            return true;
        }
    };

    $handler->registerFormatterInstance($customFormatter);

    $result = $handler
        ->addFormatted('test', 'hello', 'custom')
        ->replace('Result: {test}');

    expect($result)->toBe('Result: Custom: HELLO');
});

it('can handle formatter validation', function () {
    $handler = new PlaceholderHandler;

    // Create a formatter that only formats strings
    $stringOnlyFormatter = new class implements FormatterInterface
    {
        public function format(mixed $value, mixed ...$args): string
        {
            return 'String: '.$value;
        }

        public function getName(): string
        {
            return 'string_only';
        }

        public function canFormat(mixed $value): bool
        {
            return is_string($value);
        }
    };

    $handler->registerFormatterInstance($stringOnlyFormatter);

    // Should work with string
    $result1 = $handler
        ->addFormatted('test1', 'hello', 'string_only')
        ->replace('Result: {test1}');

    expect($result1)->toBe('Result: String: hello');

    // Should fallback with non-string
    $handler->clear();
    $result2 = $handler
        ->addFormatted('test2', 123, 'string_only')
        ->replace('Result: {test2}');

    expect($result2)->toBe('Result: N/A');
});

it('formatter instances have correct names', function () {
    $dateFormatter = new DateFormatter;
    $currencyFormatter = new CurrencyFormatter;
    $upperFormatter = new UpperFormatter;

    expect($dateFormatter->getName())->toBe('date');
    expect($currencyFormatter->getName())->toBe('currency');
    expect($upperFormatter->getName())->toBe('upper');
});

it('formatter instances validate input correctly', function () {
    $dateFormatter = new DateFormatter;
    $currencyFormatter = new CurrencyFormatter;
    $upperFormatter = new UpperFormatter;

    expect($dateFormatter->canFormat('2024-01-01'))->toBeTrue();
    expect($dateFormatter->canFormat('invalid-date'))->toBeFalse();

    expect($currencyFormatter->canFormat(123.45))->toBeTrue();
    expect($currencyFormatter->canFormat('123.45'))->toBeTrue();
    expect($currencyFormatter->canFormat('invalid'))->toBeFalse();

    expect($upperFormatter->canFormat('hello'))->toBeTrue();
    expect($upperFormatter->canFormat(123))->toBeTrue();
});

it('can check if formatter is registered', function () {
    $handler = new PlaceholderHandler;

    expect($handler->hasFormatter('upper'))->toBeTrue();
    expect($handler->hasFormatter('nonexistent'))->toBeFalse();
});

it('can get all registered formatters', function () {
    $handler = new PlaceholderHandler;

    $formatters = $handler->getRegisteredFormatters();

    expect($formatters)->toContain('date');
    expect($formatters)->toContain('currency');
    expect($formatters)->toContain('upper');
    expect($formatters)->toContain('lower');
    expect($formatters)->toContain('title');
    expect($formatters)->toContain('number');
});

it('can unregister a formatter', function () {
    $handler = new PlaceholderHandler;

    expect($handler->hasFormatter('upper'))->toBeTrue();

    $handler->unregisterFormatter('upper');

    expect($handler->hasFormatter('upper'))->toBeFalse();
});

it('can register slug formatter', function () {
    $handler = new PlaceholderHandler;
    $slugFormatter = new SlugFormatter;

    $handler->registerFormatterInstance($slugFormatter);

    $result = $handler
        ->addFormatted('title', 'Hello World Test!', 'slug')
        ->replace('Slug: {title}');

    expect($result)->toBe('Slug: hello-world-test');
});

it('slug formatter can use custom separator', function () {
    $handler = new PlaceholderHandler;
    $slugFormatter = new SlugFormatter;

    $handler->registerFormatterInstance($slugFormatter);

    $result = $handler
        ->addFormatted('title', 'Hello World Test!', 'slug', '_')
        ->replace('Slug: {title}');

    expect($result)->toBe('Slug: hello_world_test');
});

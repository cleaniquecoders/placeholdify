<?php

use CleaniqueCoders\Placeholdify\BaseLetter;
use CleaniqueCoders\Placeholdify\PlaceholderHandler;

class TestLetter extends BaseLetter
{
    protected function configure(): void
    {
        $this->handler->setFallback('N/A');
    }

    public function build(mixed $data): PlaceholderHandler
    {
        return $this->handler
            ->add('name', $data['name'] ?? null)
            ->add('type', $data['type'] ?? null)
            ->addDate('date', now(), 'd/m/Y');
    }
}

it('can create and use a custom letter class', function () {
    $letter = new TestLetter;
    $data = [
        'name' => 'John Doe',
        'type' => 'Permit',
    ];

    $template = 'Letter for: {name}, Type: {type}, Date: {date}';
    $result = $letter->generate($data, $template);

    expect($result)->toContain('Letter for: John Doe, Type: Permit, Date:');
});

it('can use letter class with modifiers', function () {
    $letter = new TestLetter;
    $data = [
        'name' => 'jane doe',
        'type' => 'certificate',
    ];

    $template = 'Letter for: {name|title}, Type: {type|upper}, Date: {date}';
    $result = $letter->generateWithModifiers($data, $template);

    expect($result)->toContain('Letter for: Jane Doe, Type: CERTIFICATE, Date:');
});

it('can handle missing data with fallbacks in letter class', function () {
    $letter = new TestLetter;
    $data = []; // Empty data

    $template = 'Letter for: {name}, Type: {type}, Date: {date}';
    $result = $letter->generate($data, $template);

    expect($result)->toContain('Letter for: N/A, Type: N/A, Date:');
});

it('can access the handler from letter class', function () {
    $letter = new TestLetter;
    $handler = $letter->getHandler();

    expect($handler)->toBeInstanceOf(PlaceholderHandler::class);
});

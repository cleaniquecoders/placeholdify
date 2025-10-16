<?php

use CleaniqueCoders\Placeholdify\PlaceholderHandler;

it('can replace basic placeholders', function () {
    $handler = new PlaceholderHandler;
    $result = $handler
        ->add('name', 'John')
        ->add('age', 25)
        ->replace('Hello {name}, you are {age} years old');

    expect($result)->toBe('Hello John, you are 25 years old');
});

it('can use static process method', function () {
    $result = PlaceholderHandler::process(
        'Hello {name}!',
        ['name' => 'Jane']
    );

    expect($result)->toBe('Hello Jane!');
});

it('can add multiple placeholders at once', function () {
    $handler = new PlaceholderHandler;
    $result = $handler
        ->addMany([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
        ])
        ->replace('Name: {first_name} {last_name}, Email: {email}');

    expect($result)->toBe('Name: John Doe, Email: john@example.com');
});

it('can handle null values with fallbacks', function () {
    $handler = new PlaceholderHandler;
    $result = $handler
        ->add('name', null, 'Unknown')
        ->replace('Hello {name}');

    expect($result)->toBe('Hello Unknown');
});

it('can use nullable with multiple fallback values', function () {
    $handler = new PlaceholderHandler;
    $result = $handler
        ->addNullable('contact', null, '', 'phone@example.com', 'No contact')
        ->replace('Contact: {contact}');

    expect($result)->toBe('Contact: phone@example.com');
});

it('can format dates', function () {
    $handler = new PlaceholderHandler;
    $result = $handler
        ->addDate('date', '2024-01-15', 'd/m/Y')
        ->replace('Date: {date}');

    expect($result)->toBe('Date: 15/01/2024');
});

it('can use custom formatters', function () {
    $handler = new PlaceholderHandler;
    $handler->registerFormatter('currency', function ($value, $currency = 'USD') {
        return $currency.' '.number_format($value, 2);
    });

    $result = $handler
        ->addFormatted('amount', 1234.56, 'currency', 'MYR')
        ->replace('Amount: {amount}');

    expect($result)->toBe('Amount: MYR 1,234.56');
});

it('can use built-in formatters', function () {
    $handler = new PlaceholderHandler;
    $result = $handler
        ->addFormatted('name', 'john doe', 'upper')
        ->addFormatted('price', 99.99, 'currency', 'USD')
        ->replace('Name: {name}, Price: {price}');

    expect($result)->toBe('Name: JOHN DOE, Price: USD 99.99');
});

it('can use conditional placeholders', function () {
    $handler = new PlaceholderHandler;
    $result = $handler
        ->addIf(true, 'status', 'Active', 'Inactive')
        ->addIf(false, 'membership', 'Premium', 'Basic')
        ->replace('Status: {status}, Membership: {membership}');

    expect($result)->toBe('Status: Active, Membership: Basic');
});

it('can use lazy evaluation', function () {
    $handler = new PlaceholderHandler;
    $called = false;

    $result = $handler
        ->addLazy('expensive', function () use (&$called) {
            $called = true;

            return 'computed value';
        })
        ->replace('Result: {expensive}');

    expect($called)->toBeTrue();
    expect($result)->toBe('Result: computed value');
});

it('can register and use contexts', function () {
    $user = (object) [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'profile' => (object) ['department' => 'Engineering'],
    ];

    $handler = new PlaceholderHandler;
    $handler->registerContext('user', [
        'name' => 'name',
        'email' => 'email',
        'dept' => 'profile.department',
    ]);

    $result = $handler
        ->useContext('user', $user, 'user')
        ->replace('User: {user.name} ({user.email}) - {user.dept}');

    expect($result)->toBe('User: John Doe (john@example.com) - Engineering');
});

it('can use modifiers in templates', function () {
    $handler = new PlaceholderHandler;
    $result = $handler
        ->add('name', 'john doe')
        ->add('amount', 123.45)
        ->add('date', '2024-01-15')
        ->replaceWithModifiers('Name: {name|upper}, Amount: {amount|currency:MYR}, Date: {date|date:d/m/Y}');

    expect($result)->toBe('Name: JOHN DOE, Amount: MYR 123.45, Date: 15/01/2024');
});

it('can set custom delimiters', function () {
    $handler = new PlaceholderHandler;
    $result = $handler
        ->setDelimiter('[[', ']]')
        ->add('name', 'John')
        ->replace('Hello [[name]]');

    expect($result)->toBe('Hello John');
});

it('can clear all placeholders', function () {
    $handler = new PlaceholderHandler;
    $handler->add('name', 'John');

    expect($handler->all())->toHaveKey('name');

    $handler->clear();

    expect($handler->all())->toBeEmpty();
});

it('handles missing placeholders with fallback', function () {
    $handler = new PlaceholderHandler;
    $handler->setFallback('MISSING');

    $result = $handler->replace('Hello {unknown}');

    expect($result)->toBe('Hello MISSING');
});

<?php

// config for CleaniqueCoders/Placeholdify
return [
    /*
    |--------------------------------------------------------------------------
    | Placeholder Delimiters
    |--------------------------------------------------------------------------
    |
    | Configure the start and end delimiters for placeholders in templates.
    | Default uses curly braces: {placeholder}
    |
    */
    'delimiter' => [
        'start' => '{',
        'end' => '}',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Fallback Value
    |--------------------------------------------------------------------------
    |
    | The default value to use when a placeholder is not found or is null.
    | This can be overridden per placeholder.
    |
    */
    'fallback' => 'N/A',

    /*
    |--------------------------------------------------------------------------
    | Built-in Formatters
    |--------------------------------------------------------------------------
    |
    | Configure which built-in formatters should be automatically registered.
    | Set to false to disable a formatter, or true to enable it.
    |
    */
    'built_in_formatters' => [
        'date' => true,
        'currency' => true,
        'number' => true,
        'upper' => true,
        'lower' => true,
        'title' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Formatter Classes
    |--------------------------------------------------------------------------
    |
    | Register custom formatter classes that implement FormatterInterface.
    | Only class-based formatters are supported for consistency and type safety.
    |
    */
    'formatters' => [
        // Example:
        // 'slug' => \App\Formatters\SlugFormatter::class,
        // 'phone' => \App\Formatters\PhoneFormatter::class,
    ],    /*
    |--------------------------------------------------------------------------
    | Global Contexts
    |--------------------------------------------------------------------------
    |
    | Register global context mappings that will be available across all
    | PlaceholderHandler instances.
    |
    */
    'contexts' => [
        // Example:
        // 'user' => [
        //     'name' => 'name',
        //     'email' => 'email',
        //     'role' => fn($user) => $user->roles->pluck('name')->join(', '),
        // ],
    ],
];

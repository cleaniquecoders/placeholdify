<?php

// config for CleaniqueCoders/Placeholdify
return [

    /*
    |--------------------------------------------------------------------------
    | Template Path
    |--------------------------------------------------------------------------
    |
    | The default directory path where template classes will be generated
    | when using the make:placeholdify-template command. This path is
    | relative to the app directory.
    |
    */
    'template_path' => 'app/Services/Placeholders',

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
    ],

    /*
    |--------------------------------------------------------------------------
    | Context Classes
    |--------------------------------------------------------------------------
    |
    | Register context classes that implement ContextInterface.
    | These provide reusable mappings for extracting data from objects.
    |
    */
    'contexts' => [
        \CleaniqueCoders\Placeholdify\Contexts\UserContext::class,

        // Add your custom context classes here:
        // \App\Contexts\CustomerContext::class,
        // \App\Contexts\OrderContext::class,
    ],
];

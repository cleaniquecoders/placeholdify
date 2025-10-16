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
    | Global Formatters
    |--------------------------------------------------------------------------
    |
    | Register global formatters that will be available across all
    | PlaceholderHandler instances.
    |
    */
    'formatters' => [
        // Example:
        // 'currency' => function($value, $currency = 'USD') {
        //     return $currency . ' ' . number_format($value, 2);
        // },
    ],

    /*
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

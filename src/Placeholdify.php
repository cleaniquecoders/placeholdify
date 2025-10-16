<?php

namespace CleaniqueCoders\Placeholdify;

/**
 * Placeholdify main class - acts as a factory for PlaceholderHandler
 */
class Placeholdify
{
    /**
     * Create a new PlaceholderHandler instance
     */
    public static function create(): PlaceholderHandler
    {
        return new PlaceholderHandler;
    }

    /**
     * Quick process method
     */
    public static function process(string $content, array $placeholders, string $delimiter = '{}'): string
    {
        return PlaceholderHandler::process($content, $placeholders, $delimiter);
    }
}

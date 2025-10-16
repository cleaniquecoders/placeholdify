<?php

namespace CleaniqueCoders\Placeholdify;

abstract class PlaceholdifyBase
{
    protected PlaceholderHandler $handler;

    public function __construct()
    {
        $this->handler = new PlaceholderHandler;
        $this->configure();
    }

    /**
     * Configure the placeholder handler
     */
    protected function configure(): void
    {
        // Override in child classes for custom configuration
    }

    /**
     * Build the placeholder handler with data
     */
    abstract public function build(mixed $data): PlaceholderHandler;

    /**
     * Generate the final content
     */
    public function generate(mixed $data, string $template): string
    {
        return $this->build($data)->replace($template);
    }

    /**
     * Generate content with modifier support
     */
    public function generateWithModifiers(mixed $data, string $template): string
    {
        return $this->build($data)->replaceWithModifiers($template);
    }

    /**
     * Get the placeholder handler
     */
    public function getHandler(): PlaceholderHandler
    {
        return $this->handler;
    }
}

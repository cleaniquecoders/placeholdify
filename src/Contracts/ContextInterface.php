<?php

namespace CleaniqueCoders\Placeholdify\Contracts;

interface ContextInterface
{
    /**
     * Get the context name/identifier
     */
    public function getName(): string;

    /**
     * Get the mapping configuration for the context
     *
     * @return array The mapping array with keys as placeholder names and values as:
     *               - string: property name (supports dot notation)
     *               - Closure: callback function that receives the object
     *               - array: configuration with 'property' and optional 'formatter' keys
     */
    public function getMapping(): array;

    /**
     * Validate if the object can be processed by this context
     *
     * @param  mixed  $object  The object to validate
     */
    public function canProcess(mixed $object): bool;

    /**
     * Get the supported object type(s) for this context
     *
     * @return string|array The class name(s) this context supports
     */
    public function getSupportedTypes(): string|array;
}

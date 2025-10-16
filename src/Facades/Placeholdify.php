<?php

namespace CleaniqueCoders\Placeholdify\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \CleaniqueCoders\Placeholdify\Placeholdify
 */
class Placeholdify extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \CleaniqueCoders\Placeholdify\Placeholdify::class;
    }
}

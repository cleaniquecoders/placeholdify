<?php

namespace CleaniqueCoders\Placeholdify\Tests;

use CleaniqueCoders\Placeholdify\PlaceholderHandler;

class BasicTest extends TestCase
{
    public function test_basic_placeholder_replacement()
    {
        $handler = new PlaceholderHandler;
        $result = $handler
            ->add('name', 'John')
            ->replace('Hello {name}');

        $this->assertEquals('Hello John', $result);
    }

    public function test_static_process_method()
    {
        $result = PlaceholderHandler::process(
            'Hello {name}!',
            ['name' => 'Jane']
        );

        $this->assertEquals('Hello Jane!', $result);
    }

    public function test_date_formatting()
    {
        $handler = new PlaceholderHandler;
        $result = $handler
            ->addDate('date', '2024-01-15', 'd/m/Y')
            ->replace('Date: {date}');

        $this->assertEquals('Date: 15/01/2024', $result);
    }
}

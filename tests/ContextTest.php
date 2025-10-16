<?php

namespace Tests;

use CleaniqueCoders\Placeholdify\Contexts\UserContext;
use CleaniqueCoders\Placeholdify\PlaceholderHandler;
use PHPUnit\Framework\TestCase;
use stdClass;

class ContextTest extends TestCase
{
    public function test_user_context_registration()
    {
        $handler = new PlaceholderHandler;
        $userContext = new UserContext;

        $handler->registerContext($userContext);

        $this->assertTrue($handler->hasContext('user'));
        $this->assertContains('user', $handler->getRegisteredContexts());
        $this->assertContains('user', $handler->getRegisteredContextInstances());
    }

    public function test_user_context_with_object()
    {
        $handler = new PlaceholderHandler;
        $userContext = new UserContext;
        $handler->registerContext($userContext);

        // Create a mock user object
        $user = new stdClass;
        $user->id = 1;
        $user->name = 'John Doe';
        $user->email = 'john@example.com';
        $user->first_name = 'John';
        $user->last_name = 'Doe';

        $handler->useContext('user', $user, 'customer');

        $template = 'Hello {customer.name} ({customer.email}), your full name is {customer.full_name}';
        $result = $handler->replace($template);

        $this->assertEquals('Hello John Doe (john@example.com), your full name is John Doe', $result);
    }

    public function test_user_context_initials_generation()
    {
        $handler = new PlaceholderHandler;
        $userContext = new UserContext;
        $handler->registerContext($userContext);

        $user = new stdClass;
        $user->first_name = 'John';
        $user->last_name = 'Doe';

        $handler->useContext('user', $user, 'user');

        $template = 'Initials: {user.initials}';
        $result = $handler->replace($template);

        $this->assertEquals('Initials: JD', $result);
    }

    public function test_user_context_can_process_validation()
    {
        $userContext = new UserContext;

        // Valid user-like object
        $user = new stdClass;
        $user->name = 'John Doe';
        $user->email = 'john@example.com';

        $this->assertTrue($userContext->canProcess($user));

        // Invalid object
        $nonUser = new stdClass;
        $nonUser->unrelated_property = 'value';

        $this->assertFalse($userContext->canProcess($nonUser));

        // Non-object
        $this->assertFalse($userContext->canProcess('string'));
        $this->assertFalse($userContext->canProcess([]));
        $this->assertFalse($userContext->canProcess(null));
    }
}

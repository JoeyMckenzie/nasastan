<?php

declare(strict_types=1);

namespace Tests\Examples\Rule_9;

use stdClass;

final class PointerDereferencing
{
    public function methodChaining(): void
    {
        $foo = new stdClass();

        // Violation: Multiple levels of method chaining
        $result = $foo->getService()->callMethod();

        // Allowed: Single level of method call
        $service = $foo->getService();
        $result = $service->callMethod();

        // Violation: Multiple levels of property access
        $value = $foo->property->nestedProperty;

        // Allowed: Single level of property access
        $property = $foo->property;
        $value = $property->nestedProperty;

        // Violation: Array access on property
        $item = $foo->items['key'];

        // Allowed: Array access on variable
        $items = $foo->items;
        $item = $items['key'];

        // Violation: Variable function (function pointer)
        $callback = 'someFunction';
        $result = $callback();

        // Violation: Closure (function pointer)
        $closure = function () {
            return 'result';
        };

        // Violation: Callable array
        $callable = [$this, 'methodName'];
        call_user_func($callable);

        // Violation: Method call on static call
        $result = SomeClass::getInstance()->doSomething();

        // Allowed: Storing static call result first
        $instance = SomeClass::getInstance();
        $result = $instance->doSomething();
    }
}

final class SomeClass
{
    public static function getInstance(): self
    {
        return new self();
    }

    public function doSomething(): string
    {
        return 'something';
    }
}

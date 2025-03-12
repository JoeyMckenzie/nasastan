<?php

declare(strict_types=1);

namespace Tests\Examples\Rule_9;

use stdClass;

final class ConfigurablePointerDereferencing
{
    public function complexMethodChains(): void
    {
        $foo = new stdClass();

        // Violation: Three levels of method chaining
        $result = $foo->getService()->getManager()->process();

        // Allowed with config maxAllowedDereferences = 2
        $service = $foo->getService()->getManager();
        $result = $service->process();

        // Violation: Three levels of property access
        $value = $foo->service->manager->config;

        // Allowed with config maxAllowedDereferences = 2
        $manager = $foo->service->manager;
        $value = $manager->config;

        // This is a more complex case with mixed property and method calls
        $mixed = $foo->service->getConfig()->value;
    }

    public function allowedComplexChains(): void
    {
        $foo = new stdClass();

        $service = $foo->getService();
        $manager = $service->getManager();
        $result = $manager->process();

        $service = $foo->service;
        $manager = $service->manager;
        $value = $manager->config;
    }
}

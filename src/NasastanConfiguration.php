<?php

declare(strict_types=1);

namespace Nasastan;

final class NasastanConfiguration
{
    public function __construct(
        public float $assertionDensity = 0.02,
        public int $minAssertions = 2,
        public int $functionSizeLimit = 60,
        public array $initMethods = ['__construct', 'initialize', 'init', 'setup', 'boot', 'register'],
        public array $ignoredFunctions = ['array_push', 'array_unshift', 'session_start', 'header', 'setcookie', 'error_log', 'trigger_error'],
        public bool $allowFluentInterfaces = true
    ) {
        //
    }
}

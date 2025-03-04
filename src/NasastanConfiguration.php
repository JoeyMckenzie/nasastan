<?php

namespace Nasastan;

class NasastanConfiguration
{

    /**
     * @var int Maximum function size in lines (Rule #4)
     */
    public int $functionSizeLimit {
        get {
            return $this->functionSizeLimit;
        }
    }

    /**
     * @var float Minimum assertion density (Rule #5)
     */
    public float $assertionDensity {
        get {
            return $this->assertionDensity;
        }
    }

    /**
     * @var int Minimum number of assertions per method (Rule #6)
     */
    public int $minAssertions {
        get {
            return $this->minAssertions;
        }
    }

    /**
     * @var array<string> Methods considered initialization methods (Rule #3)
     */
    public array $initMethods {
        get {
            return $this->initMethods;
        }
    }

    /**
     * @var array<string> Functions whose return values can be ignored (Rule #8)
     */
    public array $ignoredFunctions {
        get {
            return $this->ignoredFunctions;
        }
    }

    /**
     * @var bool Whether to allow fluent interface calls (Rule #8)
     */
    public bool $allowFluentInterfaces {
        get {
            return $this->allowFluentInterfaces;
        }
    }

    public function __construct(
        float $assertionDensity = 0.02,
        int   $minAssertions = 2,
        int   $functionSizeLimit = 60,
        array $initMethods = ['__construct', 'initialize', 'init', 'setup', 'boot', 'register'],
        array $ignoredFunctions = ['array_push', 'array_unshift', 'session_start', 'header', 'setcookie', 'error_log', 'trigger_error'],
        bool $allowFluentInterfaces = true
    )
    {
        $this->assertionDensity = $assertionDensity;
        $this->minAssertions = $minAssertions;
        $this->functionSizeLimit = $functionSizeLimit;
        $this->initMethods = $initMethods;
        $this->ignoredFunctions = $ignoredFunctions;
        $this->allowFluentInterfaces = $allowFluentInterfaces;
    }
}
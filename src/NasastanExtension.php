<?php

namespace Nasastan;

use Nasastan\Rules\AssertionDensityRule;
use Nasastan\Rules\CheckReturnValueRule;
use Nasastan\Rules\FixedLoopBoundsRule;
use Nasastan\Rules\FunctionSizeRule;
use Nasastan\Rules\NoComplexFlowConstructsRule;
use Nasastan\Rules\NoDynamicMemoryAllocationRule;
use Nasastan\Rules\NoRecursionRule;
use Nasastan\Rules\StrictTypesRule;
use Nasastan\Rules\VariableScopeRule;
use PHPStan\Rules\Rule;

class NasastanExtension
{
    private NasastanConfiguration $configuration;

    public function __construct(NasastanConfiguration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @return Rule[]
     */
    public function getRules(): array
    {
        return [
            new NoComplexFlowConstructsRule(),
            new NoRecursionRule(),
            new FixedLoopBoundsRule(),
            new NoDynamicMemoryAllocationRule($this->configuration),
            new FunctionSizeRule($this->configuration),
            new AssertionDensityRule($this->configuration, $this->configuration),
            new VariableScopeRule(),
            new CheckReturnValueRule(),
            new StrictTypesRule(),
        ];
    }
}
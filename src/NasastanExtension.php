<?php

declare(strict_types=1);

namespace Nasastan;

use Nasastan\Rules\NoComplexFlowConstructsRule;
use PHPStan\Rules\Rule;

/**
 * @internal
 */
final readonly class NasastanExtension
{
    public function __construct()
    {
        //
    }

    /**
     * @return Rule[]
     */
    public function getRules(): array
    {
        return [
            new NoComplexFlowConstructsRule(),
            // new NoRecursionRule(),
            // new FixedLoopBoundsRule(),
            // new NoDynamicMemoryAllocationRule($this->configuration),
            // new FunctionSizeRule($this->configuration),
            // new AssertionDensityRule($this->configuration, $this->configuration),
            // new VariableScopeRule(),
            // new CheckReturnValueRule(),
            // new StrictTypesRule(),
        ];
    }
}

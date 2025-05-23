<?php

declare(strict_types=1);

namespace Tests\Rules\NoComplexFlowConstructs\Examples;

/**
 * Sample file containing a recursive function that should trigger the rule.
 */
function factorial(int $n): int
{
    if ($n <= 1) {
        return 1;
    }

    return $n * factorial($n - 1); // Direct recursion, should be detected
}

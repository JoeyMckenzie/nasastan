<?php

declare(strict_types=1);

namespace Tests\Rules\NoComplexFlowConstructs\Samples;

/**
 * Sample file containing a recursive function that should trigger the rule.
 */
function factorial(int $n): int
{
    if ($n <= 1) {
        return 1;
    }

    return $n * factorial($n - 1);
}

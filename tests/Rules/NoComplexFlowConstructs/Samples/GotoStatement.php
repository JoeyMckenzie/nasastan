<?php

declare(strict_types=1);

namespace Tests\Rules\NoComplexFlowConstructs\Samples;

/**
 * Sample file containing a goto statement that should trigger the rule.
 */
function testGotoFunction(): int
{
    $i = 0;
    start:
    $i++;
    goto start;
}

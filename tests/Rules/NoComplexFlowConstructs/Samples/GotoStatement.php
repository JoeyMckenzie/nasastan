<?php

declare(strict_types=1);

namespace Tests\Rules\NoComplexFlowConstructs\Samples;

/**
 * Sample file containing a goto statement that should trigger the rule.
 */
function testGotoFunction()
{
    $i = 0;
    start:
    $i++;
    if ($i < 10) {
        goto start;
    }

    return $i;
}

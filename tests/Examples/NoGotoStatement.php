<?php

declare(strict_types=1);

namespace Tests\Rules\NoComplexFlowConstructs\Examples;

/**
 * Sample file with no goto statements.
 */
function testNoGotoFunction(): int
{
    $i = 0;
    while ($i < 10) {
        $i++;
    }

    return $i;
}

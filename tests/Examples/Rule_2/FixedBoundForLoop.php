<?php

declare(strict_types=1);

namespace Tests\Examples\Rule_2;

/**
 * Class with examples of for loops with fixed bounds
 */
final class FixedBoundForLoop
{
    private const int MAX_ITERATIONS = 50;

    public function forLoopWithLiteral(): void
    {
        // Should pass: simple fixed bound with literal
        for ($i = 0; $i < 10; $i++) {
            echo $i;
        }
    }

    public function forLoopWithConstant(): void
    {
        // Should pass: using a class constant as upper bound
        for ($i = 0; $i < self::MAX_ITERATIONS; $i++) {
            echo $i;
        }
    }
}

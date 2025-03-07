<?php

declare(strict_types=1);

namespace Tests\Examples\Rule_2;

/**
 * Class with examples of for loops with dynamic bounds
 */
final class DynamicBoundForLoop
{
    public function forLoopWithDynamicBound(): void
    {
        // Should fail: bound determined at runtime
        $max = $this->getDynamicValue();
        for ($i = 0; $i < $max; $i++) {
            echo $i;
        }
    }

    private function getDynamicValue(): int
    {
        return random_int(1, 100);
    }
}

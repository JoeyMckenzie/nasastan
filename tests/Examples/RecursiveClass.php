<?php

declare(strict_types=1);

namespace Tests\Examples;

/**
 * Sample class containing a recursive method that should trigger the rule.
 */
final class RecursiveClass
{
    /**
     * Recursive method calculation
     */
    public function calculate(int $n): int
    {
        if ($n <= 1) {
            return 1;
        }

        return $n * $this->calculate($n - 1); // Direct recursion, should be detected
    }

    /**
     * Non-recursive method
     */
    public function nonRecursiveMethod(int $n): int
    {
        $result = 1;
        for ($i = 2; $i <= $n; $i++) {
            $result *= $i;
        }

        return $result;
    }
}

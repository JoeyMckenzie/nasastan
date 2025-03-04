<?php

declare(strict_types=1);

namespace Tests\Examples;

/**
 * Sample class containing a recursive static method that should trigger the rule.
 */
final class StaticRecursiveClass
{
    /**
     * Recursive static method calculation
     */
    public static function calculate(int $n): int
    {
        if ($n <= 1) {
            return 1;
        }

        return $n * self::calculate($n - 1); // Direct recursion, should be detected
    }

    /**
     * Non-recursive static method
     */
    public static function nonRecursiveMethod(int $n): int
    {
        $result = 1;
        for ($i = 2; $i <= $n; $i++) {
            $result *= $i;
        }

        return $result;
    }
}

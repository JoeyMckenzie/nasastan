<?php

declare(strict_types=1);

namespace Examples;

final class ComplexControlFlow
{
    public function hasGoto(): void
    {
        start:
        $foo = 'bar';

        goto start;
    }

    public function factorial(int $n): int
    {
        if ($n <= 1) {
            return 1;
        }

        return $n * self::factorial($n - 1);
    }
}

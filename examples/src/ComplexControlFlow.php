<?php

declare(strict_types=1);

final class ComplexControlFlow
{
    public function hasGoto(): void
    {
        start:
        $foo = 'bar';

        goto start;
    }
}

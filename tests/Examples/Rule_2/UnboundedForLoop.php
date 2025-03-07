<?php

declare(strict_types=1);

namespace Tests\Examples\Rule_2;

/**
 * Class with examples of for loops without proper conditions
 */
final class UnboundedForLoop
{
    public function infiniteForLoop(): void
    {
        // Should fail: no condition
        for ($i = 0; ; $i++) {
            echo $i;

            // This break won't be detected by static analysis
            if ($i > 100) {
                break;
            }
        }
    }
}

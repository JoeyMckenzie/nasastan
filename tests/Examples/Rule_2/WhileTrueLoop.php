<?php

declare(strict_types=1);

namespace Tests\Examples\Rule_2;

/**
 * Class with examples of while(true) loops
 */
final class WhileTrueLoop
{
    public function infiniteWhileLoop(): void
    {
        // Should fail: while(true) is always unbounded
        while (true) {
            echo 'Processing...';

            // This break won't be detected by static analysis
            if (random_int(1, 10) === 5) {
                break;
            }
        }
    }
}

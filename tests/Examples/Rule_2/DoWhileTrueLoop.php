<?php

declare(strict_types=1);

namespace Tests\Examples\Rule_2;

/**
 * Class with examples of do-while(true) loops
 */
final class DoWhileTrueLoop
{
    public function infiniteDoWhileLoop(): void
    {
        // Should fail: do-while(true) is always unbounded
        do {
            echo 'Processing...';

            // This break won't be detected by static analysis
            if (random_int(1, 10) === 5) {
                break;
            }
        } while (true);
    }
}

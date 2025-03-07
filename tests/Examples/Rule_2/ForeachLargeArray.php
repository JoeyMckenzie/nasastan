<?php

declare(strict_types=1);

namespace Tests\Examples\Rule_2;

/**
 * Class with examples of foreach loops with arrays that exceed max iterations
 */
final class ForeachLargeArray
{
    public function foreachWithTooLargeArray(): void
    {
        // Should fail: array size exceeds configured maximum of 100
        $items = array_fill(0, 101, 'item');
        foreach ($items as $item) {
            echo $item;
        }
    }
}

<?php

declare(strict_types=1);

namespace Tests\Examples\Rule_2;

/**
 * Class with examples of foreach loops with fixed arrays
 */
final class ForeachFixedArray
{
    public function foreachWithLiteralArray(): void
    {
        // Should pass: array size is known
        $items = [1, 2, 3, 4, 5];
        foreach ($items as $item) {
            echo $item;
        }
    }

    public function foreachWithFixedArrayVar(): void
    {
        // Should pass: array size is verifiable
        /** @var array<int, string> $names */
        $names = ['Alice', 'Bob', 'Charlie'];
        foreach ($names as $name) {
            echo $name;
        }
    }
}

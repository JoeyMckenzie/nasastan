<?php

declare(strict_types=1);

namespace Examples;

use Generator;

/**
 * Class with examples of foreach loops with generators
 */
final class ForeachWithGenerator
{
    public function foreachWithGenerator(): void
    {
        // Should fail: generator size is not verifiable
        $generator = $this->createGenerator();
        foreach ($generator as $value) {
            echo $value;
        }
    }

    /**
     * @return Generator<int, string>
     */
    private function createGenerator(): Generator
    {
        for ($i = 0; $i < 10; $i++) {
            yield "Item $i";
        }
    }
}

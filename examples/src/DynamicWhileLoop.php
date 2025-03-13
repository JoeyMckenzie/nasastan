<?php

declare(strict_types=1);

namespace Examples;

/**
 * Class with examples of while loops with dynamic conditions
 */
final class DynamicWhileLoop
{
    public function whileWithDynamicCondition(): void
    {
        // Should fail: condition determined at runtime
        $result = $this->fetchNext();
        while ($result !== null) {
            echo $result;
            $result = $this->fetchNext();
        }
    }

    private function fetchNext(): ?string
    {
        static $count = 0;

        if ($count < 10) {
            $count++;

            return 'Data '.$count;
        }

        return null;
    }
}

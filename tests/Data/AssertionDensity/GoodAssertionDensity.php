<?php

namespace Tests\Unit\Data;

class GoodAssertionDensity
{
    /**
     * This method has good assertion density (above 2%)
     * - 3 parameters with type hints (3 assertions)
     * - Return type (1 assertion)
     * - If statement (1 assertion)
     * - assert() call (1 assertion)
     *
     * Total: 6 assertions in ~30 lines = ~20% density
     */
    public function goodDensityMethod(int $param1, string $param2, array $param3): bool
    {
        // A simple method with good assertion density
        $result = false;

        if ($param1 > 0) {
            $validatedParam2 = $this->validateString($param2);
            $result = true;
        }

        // An explicit assertion
        assert($param1 >= 0, 'Parameter must be non-negative');

        // Process the array
        foreach ($param3 as $item) {
            if (is_string($item)) {
                $result = $this->processItem($item, $result);
            }
        }

        return $result;
    }

    private function validateString(string $input): string
    {
        return trim($input);
    }

    private function processItem(string $item, bool $result): bool
    {
        return strlen($item) > 0 && $result;
    }
}
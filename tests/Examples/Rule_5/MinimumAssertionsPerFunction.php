<?php

declare(strict_types=1);

namespace Tests\Examples\Rule_5;

use InvalidArgumentException;
use PHPUnit\Framework\Assert;
use Stringable;

final class MinimumAssertionsPerFunction implements Stringable
{
    /**
     * Magic methods should be skipped.
     */
    public function __toString(): string
    {
        return 'AssertionsInFunctions';
    }

    /**
     * Only one assertion and should fail.
     */
    public function notEnoughAssertions(int $value): int
    {
        assert($value > 0, 'Value must be positive');

        return $value * 2;
    }

    /**
     * Function has no assertions and should fail.
     */
    public function noAssertions(int $value): int
    {
        return $value * 2;
    }

    /**
     * Function has 2 assertions and should pass.
     */
    public function enoughAssertions(int $value): int
    {
        assert($value > 0, 'Value must be positive');

        $result = $value * 2;

        assert($result > $value, 'Result should be greater than input');

        return $result;
    }

    /**
     * Only one assertion (through exception throw) - should fail.
     */
    public function methodWithOneAssertion(string $name): void
    {
        if ($name === '') {
            throw new InvalidArgumentException('Name cannot be empty');
        }

        echo "Hello, {$name}!";
    }

    /**
     * Two assertions (through exception throws) - should pass.
     */
    public function methodWithTwoAssertions(string $name, int $age): void
    {
        if ($name === '') {
            throw new InvalidArgumentException('Name cannot be empty');
        }

        if ($age <= 0) {
            throw new InvalidArgumentException('Age must be positive');
        }

        echo "Hello, $name! You are {$age} years old.";
    }

    /**
     * This method uses test assertion methods - should pass.
     */
    public function methodWithAssertionMethods(array $data): array
    {
        Assert::assertNotEmpty($data, 'Data cannot be empty');
        Assert::assertTrue(isset($data['id']), 'ID must be set');

        return $data;
    }
}

/**
 * Global function with enough assertions - should pass.
 */
function globalFunctionWithEnoughAssertions(array $items): int
{
    assert(is_array($items), 'Items must be an array');

    $count = count($items);

    assert($count >= 0, 'Count must be non-negative');

    return $count;
}

/**
 * Global function with not enough assertions - should fail.
 */
function globalFunctionWithNotEnoughAssertions(array $items): int
{
    return count($items);
}

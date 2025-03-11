<?php

declare(strict_types=1);

namespace Nasastan;

use Exception;
use Throwable;

/**
 * An error that occurs during analysis from one of the registered Nasastan rules.
 */
final class NasastanException extends Exception
{
    private function __construct(string $ruleName, ?Throwable $previous = null)
    {
        parent::__construct(
            "An error occurred while processing rule $ruleName",
            0,
            $previous
        );
    }

    public static function from(string $ruleName, Throwable $previous): self
    {
        return new self($ruleName, $previous);
    }
}

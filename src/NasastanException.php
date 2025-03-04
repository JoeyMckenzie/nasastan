<?php

declare(strict_types=1);

namespace Nasastan;

use Exception;
use Throwable;

/**
 * @internal
 */
final class NasastanException extends Exception
{
    public function __construct(string $ruleName, ?Throwable $previous = null)
    {
        parent::__construct(
            "An error occurred while processing rule $ruleName",
            0,
            $previous
        );
    }
}

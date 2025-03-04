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
    public function __construct(string $ruleDescriptor, ?Throwable $previous = null)
    {
        parent::__construct(
            "An error occurred while processing rule $ruleDescriptor",
            0,
            $previous
        );
    }
}

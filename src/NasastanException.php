<?php

namespace Nasastan;

use Exception;
use PHPStan\ShouldNotHappenException;

class NasastanException extends Exception
{
    public function __construct(string $rule, ShouldNotHappenException $exceptionMessage)
    {
        parent::__construct(
            sprintf(
                '%s: failed to assert Nasastan rule. Reason: %s',
                $rule,
                $exceptionMessage->getMessage()
            )
        );
    }
}
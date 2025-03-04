<?php

declare(strict_types=1);

namespace Nasastan;

use PHPStan\Rules\Rule;

/**
 * @internal
 */
interface NasastanRule extends Rule
{
    public function getRuleName(): string;

    public function getRuleDescriptor(): string;
}

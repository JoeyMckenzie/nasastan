<?php

declare(strict_types=1);

namespace Nasastan;

use PhpParser\Node;
use PHPStan\Rules\Rule;

/**
 * @template TNode of Node
 *
 * @extends Rule<TNode>
 *
 * @internal
 */
interface NasastanRule extends Rule
{
    public function getRuleName(): string;

    public function getRuleDescriptor(): string;
}

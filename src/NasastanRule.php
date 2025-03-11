<?php

declare(strict_types=1);

namespace Nasastan;

use PhpParser\Node;
use PHPStan\Rules\Rule;

/**
 * NASA-based PHPStan rule all rules will inherit from.
 *
 * @template TNode of Node
 *
 * @extends Rule<TNode>
 *
 * @internal
 */
interface NasastanRule extends Rule
{
    /**
     * Name representation of the rule by order (Rule #1, Rule #2, etc.).
     */
    public function getRuleName(): string;

    /**
     * A short description of the rule.
     */
    public function getRuleDescriptor(): string;
}

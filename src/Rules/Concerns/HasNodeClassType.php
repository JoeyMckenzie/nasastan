<?php

declare(strict_types=1);

namespace Nasastan\Rules\Concerns;

use PhpParser\Node;

/**
 * Signals that a rule must handle multiple nodes and statements from parsed from the AST.
 *
 * @internal
 */
trait HasNodeClassType
{
    public function getNodeType(): string
    {
        return Node::class;
    }
}

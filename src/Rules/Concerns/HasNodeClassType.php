<?php

declare(strict_types=1);

namespace NASAStan\Rules\Concerns;

use PhpParser\Node;

/**
 * Signals that a rule will handle multiple nodes and statements from parsed from the AST.
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

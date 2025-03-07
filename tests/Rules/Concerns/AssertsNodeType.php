<?php

declare(strict_types=1);

namespace Tests\Rules\Concerns;

use PhpParser\Node;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\Test;

trait AssertsNodeType
{
    #[Test]
    public function test_node_type(): void
    {
        Assert::assertEquals(Node::class, $this->rule->getNodeType());
    }
}

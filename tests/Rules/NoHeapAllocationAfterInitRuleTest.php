<?php

declare(strict_types=1);

namespace Tests\Rules;

use NASAStan\NASAStanConfiguration;
use NASAStan\Rules\NoHeapAllocationAfterInitRule;
use PhpParser\Node;
use PHPStan\Rules\Rule;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\NASAStanRuleTestCase;

/**
 * @extends NASAStanRuleTestCase<NoHeapAllocationAfterInitRule>
 */
#[CoversClass(NoHeapAllocationAfterInitRule::class)]
final class NoHeapAllocationAfterInitRuleTest extends NASAStanRuleTestCase
{
    private NoHeapAllocationAfterInitRule $rule;

    protected function setUp(): void
    {
        $configuration = new NASAStanConfiguration();
        $this->rule = new NoHeapAllocationAfterInitRule($configuration);
    }

    #[Test]
    public function test_rule(): void
    {
        $this->analyse([__DIR__.'/../Examples/Rule_3/HeapAllocationAfterInitMethods.php'], [
            [
                'NASA Power of Ten Rule #3: Dynamic array creation is not allowed after initialization.',
                15,
            ],
            [
                'NASA Power of Ten Rule #3: Object instantiation is not allowed after initialization.',
                41,
            ],
            [
                'NASA Power of Ten Rule #3: Container method "push" that allocates memory is not allowed after initialization.',
                43,
            ],
            [
                'NASA Power of Ten Rule #3: Resource allocation function "fopen" is not allowed after initialization.',
                45,
            ],
            [
                'NASA Power of Ten Rule #3: Dynamic array creation is not allowed after initialization.',
                51,
            ],
        ]);
    }

    #[Test]
    public function test_rule_name(): void
    {
        Assert::assertEquals('NASA Power of Ten Rule #3', $this->rule->getRuleName());
    }

    #[Test]
    public function test_rule_descriptor(): void
    {
        Assert::assertEquals('Avoid heap memory allocation after initialization.', $this->rule->getRuleDescriptor());
    }

    #[Test]
    public function test_node_type(): void
    {
        Assert::assertEquals(Node::class, $this->rule->getNodeType());
    }

    #[Test]
    public function test_not_enabled_returns_no_errors(): void
    {
        $configuration = new NASAStanConfiguration(
            enabledRules: ['rule_1']
        );

        $this->rule = new NoHeapAllocationAfterInitRule($configuration);

        $this->analyse([__DIR__.'/../Examples/Rule_3/HeapAllocationAfterInitMethods.php'], []);
    }

    #[Test]
    public function test_enabled_with_bypass_returns_no_errors(): void
    {
        $configuration = new NASAStanConfiguration(
            enabledRules: ['rule_3'],
            exceptRules: ['rule_3']
        );

        $this->rule = new NoHeapAllocationAfterInitRule($configuration);

        $this->analyse([__DIR__.'/../Examples/Rule_3/HeapAllocationAfterInitMethods.php'], []);
    }

    protected function getRule(): Rule
    {
        return $this->rule;
    }
}

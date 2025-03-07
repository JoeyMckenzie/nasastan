<?php

declare(strict_types=1);

namespace Tests\Rules;

use Nasastan\Rules\FixedUpperBoundOnLoopsRule;
use PhpParser\Node\Stmt;
use PHPStan\Rules\Rule;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\Test;
use Tests\NasastanRuleTestCase;

/**
 * @extends NasastanRuleTestCase<FixedUpperBoundOnLoopsRule>
 */
final class FixedUpperBoundOnLoopsRuleTest extends NasastanRuleTestCase
{
    private readonly FixedUpperBoundOnLoopsRule $rule;

    protected function setUp(): void
    {
        $this->rule = new FixedUpperBoundOnLoopsRule(100);
    }

    #[Test]
    public function test_for_loop_with_fixed_bound_passes(): void
    {
        $this->analyse([__DIR__.'/../Examples/Rule_2/FixedBoundForLoop.php'], []);
    }

    #[Test]
    public function test_for_loop_with_no_condition_fails(): void
    {
        $this->analyse([__DIR__.'/../Examples/Rule_2/UnboundedForLoop.php'], [
            [
                'For loop must have a condition and increment to ensure fixed bounds.',
                15,
            ],
        ]);
    }

    #[Test]
    public function test_for_loop_with_dynamic_bound_fails(): void
    {
        $this->analyse([__DIR__.'/../Examples/Rule_2/DynamicBoundForLoop.php'], [
            [
                'For loop must have a fixed upper bound to prevent runaway code.',
                16,
            ],
        ]);
    }

    #[Test]
    public function test_while_true_loop_fails(): void
    {
        $this->analyse([__DIR__.'/../Examples/Rule_2/WhileTrueLoop.php'], [
            [
                'While/do-while loop with condition "true" has no upper bound.',
                15,
            ],
        ]);
    }

    #[Test]
    public function test_do_while_true_loop_fails(): void
    {
        $this->analyse([__DIR__.'/../Examples/Rule_2/DoWhileTrueLoop.php'], [
            [
                'While/do-while loop with condition "true" has no upper bound.',
                15,
            ],
        ]);
    }

    #[Test]
    public function test_while_with_dynamic_condition_fails(): void
    {
        $this->analyse([__DIR__.'/../Examples/Rule_2/WhileDynamicCondition.php'], [
            [
                'While/do-while loop must have a verifiable fixed upper bound to prevent runaway code.',
                16,
            ],
        ]);
    }

    #[Test]
    public function test_foreach_with_fixed_array_passes(): void
    {
        $this->analyse([__DIR__.'/../Examples/Rule_2/ForeachFixedArray.php'], []);
    }

    #[Test]
    public function test_foreach_with_too_large_array_fails(): void
    {
        $this->markTestSkipped('TODO: Need to figure out the unbounded check for this scenario, currently missing the brain power to do so');

        $this->analyse([__DIR__.'/../Examples/Rule_2/ForeachLargeArray.php'], [
            [
                'Foreach loop iterates over 101 items, which exceeds the configured maximum of 100 iterations.',
                14,
            ],
        ]);
    }

    #[Test]
    public function test_foreach_with_generator_fails(): void
    {
        $this->analyse([__DIR__.'/../Examples/Rule_2/ForeachGenerator.php'], [
            [
                'Foreach loop must iterate over a countable collection with a verifiable size bound.',
                18,
            ],
        ]);
    }

    #[Test]
    public function test_rule_name(): void
    {
        Assert::assertEquals('NASA Power of Ten Rule #2', $this->rule->getRuleName());
    }

    #[Test]
    public function test_rule_descriptor(): void
    {
        Assert::assertEquals('All loops must have fixed bounds. This prevents runaway code.', $this->rule->getRuleDescriptor());
    }

    #[Test]
    public function test_node_type(): void
    {
        Assert::assertEquals(Stmt::class, $this->rule->getNodeType());
    }

    protected function getRule(): Rule
    {
        return $this->rule;
    }
}

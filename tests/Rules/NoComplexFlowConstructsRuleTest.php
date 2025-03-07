<?php

declare(strict_types=1);

namespace Tests\Rules;

use Nasastan\Rules\NoComplexFlowConstructsRule;
use PhpParser\Node;
use PHPStan\Rules\Rule;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\Test;
use Tests\NasastanRuleTestCase;

/**
 * @extends NasastanRuleTestCase<NoComplexFlowConstructsRule>
 */
final class NoComplexFlowConstructsRuleTest extends NasastanRuleTestCase
{
    private readonly NoComplexFlowConstructsRule $rule;

    protected function setUp(): void
    {
        $this->rule = new NoComplexFlowConstructsRule();
    }

    #[Test]
    public function test_detects_goto_statements(): void
    {
        $this->analyse([__DIR__.'/../Examples/Rule_1/GotoStatement.php'], [
            [
                'NASA Power of Ten Rule #1: Goto statements are not allowed.',
                15,
            ],
        ]);
    }

    #[Test]
    public function test_passes_with_no_complex_flow_constructs(): void
    {
        $this->analyse([__DIR__.'/../Examples/Rule_1/NoGotoStatement.php'], []);
    }

    #[Test]
    public function test_detects_recursive_functions(): void
    {
        $this->analyse([__DIR__.'/../Examples/Rule_1/RecursiveFunction.php'], [
            [
                'NASA Power of Ten Rule #1: Recursive function calls are not allowed.',
                16,
            ],
        ]);
    }

    #[Test]
    public function test_detects_recursive_methods(): void
    {
        $this->analyse([__DIR__.'/../Examples/Rule_1/RecursiveClass.php'], [
            [
                'NASA Power of Ten Rule #1: Recursive method calls are not allowed.',
                21,
            ],
        ]);
    }

    #[Test]
    public function test_detects_recursive_static_methods(): void
    {
        $this->analyse([__DIR__.'/../Examples/Rule_1/StaticRecursiveClass.php'], [
            [
                'NASA Power of Ten Rule #1: Recursive static method calls are not allowed.',
                21,
            ],
        ]);
    }

    #[Test]
    public function test_rule_name(): void
    {
        Assert::assertEquals('NASA Power of Ten Rule #1', $this->rule->getRuleName());
    }

    #[Test]
    public function test_rule_descriptor(): void
    {
        Assert::assertEquals('Avoid complex flow constructs, such as goto and recursion.', $this->rule->getRuleDescriptor());
    }

    #[Test]
    public function test_node_type(): void
    {
        Assert::assertEquals(Node::class, $this->rule->getNodeType());
    }

    protected function getRule(): Rule
    {
        return $this->rule;
    }
}

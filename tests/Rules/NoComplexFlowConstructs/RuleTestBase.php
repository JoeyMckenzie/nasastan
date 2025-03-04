<?php

declare(strict_types=1);

namespace Tests\Rules\NoComplexFlowConstructs;

use Nasastan\Rules\NoComplexFlowConstructsRule;
use PhpParser\Node;
use PHPStan\Rules\Rule;
use Tests\AbstractRuleTestBase;

/**
 * @extends AbstractRuleTestBase<NoComplexFlowConstructsRule>
 */
final class RuleTestBase extends AbstractRuleTestBase
{
    private readonly Rule $rule;

    protected function setUp(): void
    {
        $this->rule = new NoComplexFlowConstructsRule();
    }

    public function test_detects_goto_statements(): void
    {
        $this->analyse([__DIR__.'/Examples/GotoStatement.php'], [
            [
                'NASA Power of Ten Rule #1: Goto statements are not allowed.',
                15,
            ],
        ]);
    }

    public function test_passes_with_no_complex_flow_constructs(): void
    {
        $this->analyse([__DIR__.'/Examples/NoGotoStatement.php'], []);
    }

    public function test_detects_recursive_functions(): void
    {
        $this->analyse([__DIR__.'/Examples/RecursiveFunction.php'], [
            [
                'NASA Power of Ten Rule #1: Recursive function calls are not allowed.',
                16,
            ],
        ]);
    }

    public function test_detects_recursive_methods(): void
    {
        $this->analyse([__DIR__.'/Examples/RecursiveClass.php'], [
            [
                'NASA Power of Ten Rule #1: Recursive method calls are not allowed.',
                21,
            ],
        ]);
    }

    public function test_detects_recursive_static_methods(): void
    {
        $this->analyse([__DIR__.'/Examples/StaticRecursiveClass.php'], [
            [
                'NASA Power of Ten Rule #1: Recursive static method calls are not allowed.',
                21,
            ],
        ]);
    }

    public function test_rule_name(): void
    {
        $this->assertEquals('NASA Power of Ten Rule #1', $this->rule->getRuleName());
    }

    public function test_rule_descriptor(): void
    {
        $this->assertEquals('Avoid complex flow constructs, such as goto and recursion.', $this->rule->getRuleDescriptor());
    }

    public function test_node_type(): void
    {
        $this->assertEquals(Node::class, $this->rule->getNodeType());
    }

    protected function getRule(): Rule
    {
        return $this->rule;
    }
}

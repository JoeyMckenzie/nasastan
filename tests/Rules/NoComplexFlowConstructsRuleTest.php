<?php

declare(strict_types=1);

namespace Tests\Rules;

use NASAStan\NASAStanConfiguration;
use NASAStan\Rules\NoComplexFlowConstructsRule;
use PhpParser\Node;
use PHPStan\Rules\Rule;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\NASAStanRuleTestCase;

/**
 * @extends NASAStanRuleTestCase<NoComplexFlowConstructsRule>
 */
#[CoversClass(NoComplexFlowConstructsRule::class)]
final class NoComplexFlowConstructsRuleTest extends NASAStanRuleTestCase
{
    private NoComplexFlowConstructsRule $rule;

    protected function setUp(): void
    {
        $configuration = new NASAStanConfiguration();
        $this->rule = new NoComplexFlowConstructsRule($configuration);
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

    #[Test]
    public function test_not_enabled_returns_no_errors(): void
    {
        $configuration = new NASAStanConfiguration(
            enabledRules: ['rule_2']
        );

        $this->rule = new NoComplexFlowConstructsRule($configuration);

        $this->analyse([__DIR__.'/../Examples/Rule_1/RecursiveFunction.php'], []);
    }

    #[Test]
    public function test_enabled_with_bypass_returns_no_errors(): void
    {
        $configuration = new NASAStanConfiguration(
            enabledRules: ['rule_1'],
            exceptRules: ['rule_1']
        );

        $this->rule = new NoComplexFlowConstructsRule($configuration);

        $this->analyse([__DIR__.'/../Examples/Rule_1/RecursiveFunction.php'], []);
    }

    protected function getRule(): Rule
    {
        return $this->rule;
    }
}

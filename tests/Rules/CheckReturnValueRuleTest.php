<?php

declare(strict_types=1);

namespace Tests\Rules;

use NASAStan\NASAStanConfiguration;
use NASAStan\Rules\CheckReturnValueRule;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Rules\Rule;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\NASAStanRuleTestCase;

/**
 * @extends NASAStanRuleTestCase<CheckReturnValueRule>
 */
#[CoversClass(CheckReturnValueRule::class)]
final class CheckReturnValueRuleTest extends NASAStanRuleTestCase
{
    private CheckReturnValueRule $rule;

    protected function setUp(): void
    {
        $configuration = new NASAStanConfiguration();
        $this->rule = new CheckReturnValueRule($configuration);
    }

    #[Test]
    public function test_rule(): void
    {
        $this->analyse([__DIR__.'/../Examples/Rule_7/ReturnValueUsage.php'], [
            [
                'NASA Power of Ten Rule #6: Return value of method Tests\Examples\Rule_7\ReturnValueUsage::getNonVoidValue() is not used. Either use the return value or add a @ignoreReturnValue annotation.',
                36,
            ],
            [
                'NASA Power of Ten Rule #6: Return value of method Tests\Examples\Rule_7\ReturnValueUsage::getArrayValue() is not used. Either use the return value or add a @ignoreReturnValue annotation.',
                39,
            ],
            [
                'NASA Power of Ten Rule #6: Return value of static method self::getStaticValue() is not used. Either use the return value or add a @ignoreReturnValue annotation.',
                42,
            ],
        ]);
    }

    #[Test]
    public function test_rule_name(): void
    {
        Assert::assertEquals('NASA Power of Ten Rule #7', $this->rule->getRuleName());
    }

    #[Test]
    public function test_rule_descriptor(): void
    {
        Assert::assertEquals(
            'Check the return value of all non-void functions, or cast to void to indicate the return value is useless.',
            $this->rule->getRuleDescriptor()
        );
    }

    #[Test]
    public function test_node_type(): void
    {
        Assert::assertEquals(Expression::class, $this->rule->getNodeType());
    }

    #[Test]
    public function test_not_enabled_returns_no_errors(): void
    {
        $configuration = new NASAStanConfiguration(
            enabledRules: ['rule_1']
        );

        $this->rule = new CheckReturnValueRule($configuration);

        $this->analyse([__DIR__.'/../Examples/Rule_7/ReturnValueUsage.php'], []);
    }

    #[Test]
    public function test_enabled_with_bypass_returns_no_errors(): void
    {
        $configuration = new NASAStanConfiguration(
            enabledRules: ['rule_7'],
            exceptRules: ['rule_7']
        );

        $this->rule = new CheckReturnValueRule($configuration);

        $this->analyse([__DIR__.'/../Examples/Rule_7/ReturnValueUsage.php'], []);
    }

    protected function getRule(): Rule
    {
        return $this->rule;
    }
}

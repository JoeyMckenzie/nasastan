<?php

declare(strict_types=1);

namespace Tests\Rules;

use Nasastan\NasastanConfiguration;
use Nasastan\Rules\CheckReturnValueRule;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Rules\Rule;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\Test;
use Tests\NasastanRuleTestCase;

/**
 * @extends NasastanRuleTestCase<CheckReturnValueRule>
 */
final class CheckReturnValueRuleTest extends NasastanRuleTestCase
{
    private readonly CheckReturnValueRule $rule;

    protected function setUp(): void
    {
        $configuration = new NasastanConfiguration();
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
        Assert::assertEquals('NASA Power of Ten Rule #6', $this->rule->getRuleName());
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

    protected function getRule(): Rule
    {
        return $this->rule;
    }
}

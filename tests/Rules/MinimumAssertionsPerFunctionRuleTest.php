<?php

declare(strict_types=1);

namespace Tests\Rules;

use NASAStan\NASAStanConfiguration;
use NASAStan\Rules\MinimumAssertionsPerFunctionRule;
use PHPStan\Rules\Rule;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\NASAStanRuleTestCase;
use Tests\Rules\Concerns\AssertsNodeType;

/**
 * @extends NASAStanRuleTestCase<MinimumAssertionsPerFunctionRule>
 */
#[CoversClass(MinimumAssertionsPerFunctionRule::class)]
final class MinimumAssertionsPerFunctionRuleTest extends NASAStanRuleTestCase
{
    use AssertsNodeType;

    private MinimumAssertionsPerFunctionRule $rule;

    protected function setUp(): void
    {
        $configuration = new NASAStanConfiguration();
        $this->rule = new MinimumAssertionsPerFunctionRule($configuration);
    }

    #[Test]
    public function test_assertions_rules(): void
    {
        $this->analyse([__DIR__.'/../Examples/Rule_5/MinimumAssertionsPerFunction.php'], [
            [
                'NASA Power of Ten Rule #5: Function "notEnoughAssertions" contains 1 assertions, but at least 2 are required.',
                24,
            ],
            [
                'NASA Power of Ten Rule #5: Function "noAssertions" contains 0 assertions, but at least 2 are required.',
                34,
            ],
            [
                'NASA Power of Ten Rule #5: Function "methodWithOneAssertion" contains 1 assertions, but at least 2 are required.',
                56,
            ],
            [
                'NASA Power of Ten Rule #5: Function "globalFunctionWithNotEnoughAssertions" contains 0 assertions, but at least 2 are required.',
                110,
            ],
        ]);
    }

    #[Test]
    public function test_rule_name(): void
    {
        Assert::assertEquals('NASA Power of Ten Rule #5', $this->rule->getRuleName());
    }

    #[Test]
    public function test_rule_descriptor(): void
    {
        Assert::assertEquals('Use a minimum of two runtime assertions per function.', $this->rule->getRuleDescriptor());
    }

    #[Test]
    public function test_not_enabled_returns_no_errors(): void
    {
        $configuration = new NASAStanConfiguration(
            enabledRules: ['rule_1']
        );

        $this->rule = new MinimumAssertionsPerFunctionRule($configuration);

        $this->analyse([__DIR__.'/../Examples/Rule_5/MinimumAssertionsPerFunction.php'], []);
    }

    #[Test]
    public function test_enabled_with_bypass_returns_no_errors(): void
    {
        $configuration = new NASAStanConfiguration(
            enabledRules: ['rule_5'],
            exceptRules: ['rule_5']
        );

        $this->rule = new MinimumAssertionsPerFunctionRule($configuration);

        $this->analyse([__DIR__.'/../Examples/Rule_5/MinimumAssertionsPerFunction.php'], []);
    }

    protected function getRule(): Rule
    {
        return $this->rule;
    }
}

<?php

declare(strict_types=1);

namespace Tests\Rules;

use Nasastan\NasastanConfiguration;
use Nasastan\Rules\MinimumAssertionsPerFunctionRule;
use PHPStan\Rules\Rule;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\NasastanRuleTestCase;
use Tests\Rules\Concerns\AssertsNodeType;

/**
 * @extends NasastanRuleTestCase<MinimumAssertionsPerFunctionRule>
 */
#[CoversClass(MinimumAssertionsPerFunctionRule::class)]
final class MinimumAssertionsPerFunctionRuleTest extends NasastanRuleTestCase
{
    use AssertsNodeType;

    private readonly MinimumAssertionsPerFunctionRule $rule;

    protected function setUp(): void
    {
        $configuration = new NasastanConfiguration();
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

    protected function getRule(): Rule
    {
        return $this->rule;
    }
}

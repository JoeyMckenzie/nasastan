<?php

declare(strict_types=1);

namespace Tests\Rules;

use NASAStan\NASAStanConfiguration;
use NASAStan\Rules\RestrictFunctionLengthRule;
use PHPStan\Rules\Rule;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\NASAStanRuleTestCase;
use Tests\Rules\Concerns\AssertsNodeType;

/**
 * @extends NASAStanRuleTestCase<RestrictFunctionLengthRule>
 */
#[CoversClass(RestrictFunctionLengthRule::class)]
final class RestrictFunctionLengthRuleTest extends NASAStanRuleTestCase
{
    use AssertsNodeType;

    private RestrictFunctionLengthRule $rule;

    protected function setUp(): void
    {
        $configuration = new NASAStanConfiguration();
        $this->rule = new RestrictFunctionLengthRule($configuration);
    }

    #[Test]
    public function test_default_rule(): void
    {
        $this->analyse([__DIR__.'/../Examples/Rule_4/FunctionLengthInvalid.php'], [
            [
                'NASA Power of Ten Rule #4: Method "reallyLongMethod" has 63 lines which exceeds the maximum of 60 lines (single printed page).',
                65,
            ],
        ]);
    }

    #[Test]
    public function test_stricter_max_lines_rule(): void
    {
        $configuration = new NASAStanConfiguration(maxLines: 20);
        $this->rule = new RestrictFunctionLengthRule($configuration);

        $this->analyse([__DIR__.'/../Examples/Rule_4/FunctionLengthInvalid.php'], [
            [
                'NASA Power of Ten Rule #4: Method "longMethod" has 34 lines which exceeds the maximum of 20 lines (single printed page).',
                27,
            ],
            [
                'NASA Power of Ten Rule #4: Method "reallyLongMethod" has 63 lines which exceeds the maximum of 20 lines (single printed page).',
                65,
            ],
        ]);
    }

    #[Test]
    public function test_excluding_comments_and_blank_lines(): void
    {
        $configuration = new NASAStanConfiguration(
            maxLines: 30,
            includeComments: false,
            includeBlankLines: false,
        );
        $this->rule = new RestrictFunctionLengthRule($configuration);

        $this->analyse([__DIR__.'/../Examples/Rule_4/FunctionLengthInvalid.php'], [
            [
                'NASA Power of Ten Rule #4: Method "reallyLongMethod" has 49 lines which exceeds the maximum of 30 lines (single printed page).',
                65,
            ],
        ]);
    }

    #[Test]
    public function test_rule_name(): void
    {
        Assert::assertEquals('NASA Power of Ten Rule #4', $this->rule->getRuleName());
    }

    #[Test]
    public function test_rule_descriptor(): void
    {
        Assert::assertEquals('Restrict functions to a single printed page.', $this->rule->getRuleDescriptor());
    }

    #[Test]
    public function test_not_enabled_returns_no_errors(): void
    {
        $configuration = new NASAStanConfiguration(
            enabledRules: ['rule_1']
        );

        $this->rule = new RestrictFunctionLengthRule($configuration);

        $this->analyse([__DIR__.'/../Examples/Rule_4/FunctionLengthInvalid.php'], []);
    }

    #[Test]
    public function test_enabled_with_bypass_returns_no_errors(): void
    {
        $configuration = new NASAStanConfiguration(
            enabledRules: ['rule_4'],
            exceptRules: ['rule_4']
        );

        $this->rule = new RestrictFunctionLengthRule($configuration);

        $this->analyse([__DIR__.'/../Examples/Rule_4/FunctionLengthInvalid.php'], []);
    }

    protected function getRule(): Rule
    {
        return $this->rule;
    }
}

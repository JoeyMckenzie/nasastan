<?php

declare(strict_types=1);

namespace Tests\Rules;

use Nasastan\NasastanConfiguration;
use Nasastan\Rules\RestrictFunctionLengthRule;
use PHPStan\Rules\Rule;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\NasastanRuleTestCase;
use Tests\Rules\Concerns\AssertsNodeType;

/**
 * @extends NasastanRuleTestCase<RestrictFunctionLengthRule>
 */
#[CoversClass(RestrictFunctionLengthRule::class)]
final class RestrictFunctionLengthRuleTest extends NasastanRuleTestCase
{
    use AssertsNodeType;

    private RestrictFunctionLengthRule $rule;

    protected function setUp(): void
    {
        $configuration = new NasastanConfiguration();
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
        $configuration = new NasastanConfiguration(maxLines: 20);
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
        $configuration = new NasastanConfiguration(
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

    protected function getRule(): Rule
    {
        return $this->rule;
    }
}

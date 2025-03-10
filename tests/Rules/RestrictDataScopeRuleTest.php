<?php

declare(strict_types=1);

namespace Tests\Rules;

use Nasastan\NasastanConfiguration;
use Nasastan\Rules\RestrictDataScopeRule;
use PhpParser\Node;
use PHPStan\Rules\Rule;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\Test;
use Tests\NasastanRuleTestCase;

/**
 * @extends NasastanRuleTestCase<RestrictDataScopeRule>
 */
final class RestrictDataScopeRuleTest extends NasastanRuleTestCase
{
    private readonly RestrictDataScopeRule $rule;

    protected function setUp(): void
    {
        $configuration = new NasastanConfiguration(
            maxClassProperties: 3,
            allowedPublicProperties: ['id', 'name', 'created_*', 'updated_*']
        );
        $this->rule = new RestrictDataScopeRule($configuration);
    }

    #[Test]
    public function test_restricted_data_scope_rule(): void
    {
        $this->analyse([__DIR__.'/../Examples/Rule_6/RestrictedDataScope.php'], [
            [
                'NASA Power of Ten Rule #6: Class "TooManyProperties" has 4 properties, but the maximum allowed is 3.',
                30,
            ],
            [
                'NASA Power of Ten Rule #6: Class "PublicPropertyExample" has 6 properties, but the maximum allowed is 3.',
                52,
            ],
            [
                'NASA Power of Ten Rule #6: Public property "status" in class "PublicPropertyExample" violates data scope restriction. Consider making it private or protected.',
                52,
            ],
            [
                'NASA Power of Ten Rule #6: Public property "description" in class "PublicPropertyExample" violates data scope restriction. Consider making it private or protected.',
                52,
            ],
            [
                'NASA Power of Ten Rule #6: Class "AllowedPublicPropertiesExample" has 4 properties, but the maximum allowed is 3.',
                99,
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
        Assert::assertEquals('Restrict the scope of data to the smallest possible.', $this->rule->getRuleDescriptor());
    }

    #[Test]
    public function test_node_type(): void
    {
        Assert::assertEquals(Node\Stmt\Class_::class, $this->rule->getNodeType());
    }

    protected function getRule(): Rule
    {
        return $this->rule;
    }
}

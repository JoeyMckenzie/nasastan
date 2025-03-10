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
    private RestrictDataScopeRule $rule;

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
                'NASA Power of Ten Rule #6: Class "PublicPropertyExample" has 6 properties, but the maximum allowed is 3.',
                35,
            ],
            [
                'NASA Power of Ten Rule #6: Public property "status" in class "PublicPropertyExample" violates data scope restriction. Consider making it private or protected.',
                35,
            ],
            [
                'NASA Power of Ten Rule #6: Public property "description" in class "PublicPropertyExample" violates data scope restriction. Consider making it private or protected.',
                35,
            ],
        ]);
    }

    #[Test]
    public function test_wildcard_pattern_matching(): void
    {
        $configuration = new NasastanConfiguration(
            maxClassProperties: 10,
            allowedPublicProperties: ['id', 'user_*', '*_date', '*_id']
        );

        $this->rule = new RestrictDataScopeRule($configuration);

        $this->analyse([__DIR__.'/../Examples/Rule_6/PatternMatching.php'], [
            [
                'NASA Power of Ten Rule #6: Public property "name" in class "PatternMatching" violates data scope restriction. Consider making it private or protected.',
                10,
            ],
            [
                'NASA Power of Ten Rule #6: Public property "description" in class "PatternMatching" violates data scope restriction. Consider making it private or protected.',
                10,
            ],
        ]);
    }

    #[Test]
    public function test_class_based_edge_cases(): void
    {
        $configuration = new NasastanConfiguration(
            maxClassProperties: 5,
            allowedPublicProperties: []
        );

        $this->rule = new RestrictDataScopeRule($configuration);

        $this->analyse([__DIR__.'/../Examples/Rule_6/EdgeCases.php'], [
            [
                'NASA Power of Ten Rule #6: Public property "prop" in class "EmptyClass" violates data scope restriction. Consider making it private or protected.',
                11,
            ],
            // Anonymous class with too many properties
            [
                '#NASA Power of Ten Rule #6: Class "AnonymousClass[a-z0-9]+" has 6 properties, but the maximum allowed is 5\.#',
                22,
            ],
            // Anonymous class with disallowed public property
            [
                '#NASA Power of Ten Rule #6: Public property "publicProp" in class "AnonymousClass[a-z0-9]+" violates data scope restriction\. Consider making it private or protected\.#',
                53,
            ],
            // Abstract class with disallowed public property
            [
                'NASA Power of Ten Rule #6: Public property "publicProp" in class "AbstractClass" violates data scope restriction. Consider making it private or protected.',
                86,
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

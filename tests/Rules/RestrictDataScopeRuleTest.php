<?php

declare(strict_types=1);

namespace Tests\Rules;

use NASAStan\NASAStanConfiguration;
use NASAStan\Rules\RestrictDataScopeRule;
use PhpParser\Node;
use PHPStan\Rules\Rule;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\NASAStanRuleTestCase;

/**
 * @extends NASAStanRuleTestCase<RestrictDataScopeRule>
 */
#[CoversClass(RestrictDataScopeRule::class)]
final class RestrictDataScopeRuleTest extends NASAStanRuleTestCase
{
    private RestrictDataScopeRule $rule;

    protected function setUp(): void
    {
        $configuration = new NASAStanConfiguration(
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
                'NASA Power of Ten Rule #6: Class "TooManyProperties" has 6 properties, but the maximum allowed is 3.',
                30,
            ],
            [
                'NASA Power of Ten Rule #6: Class "TooManyPromotedPropertiesClass" has 6 properties, but the maximum allowed is 3.',
                48,
            ],
            [
                'NASA Power of Ten Rule #6: Class "MixOfTooManyPropertiesClass" has 6 properties, but the maximum allowed is 3.',
                65,
            ],
            [
                'NASA Power of Ten Rule #6: Class "WhitelistedProperties" has 6 properties, but the maximum allowed is 3.',
                85,
            ],
            [
                'NASA Power of Ten Rule #6: Public property "status" in class "WhitelistedProperties" violates data scope restriction. Consider making it private or protected.',
                85,
            ],
            [
                'NASA Power of Ten Rule #6: Public property "description" in class "WhitelistedProperties" violates data scope restriction. Consider making it private or protected.',
                85,
            ],
        ]);
    }

    #[Test]
    public function test_wildcard_pattern_matching(): void
    {
        $configuration = new NASAStanConfiguration(
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
        $configuration = new NASAStanConfiguration(
            maxClassProperties: 5,
            allowedPublicProperties: []
        );

        $this->rule = new RestrictDataScopeRule($configuration);
        $errors = $this->gatherAnalyserErrors([__DIR__.'/../Examples/Rule_6/EdgeCases.php']);

        // Create a map of line numbers to expected regex patterns
        $assertions = [
            10 => '/NASA Power of Ten Rule #6: Public property "prop" in class "EmptyClass" violates data scope restriction/',
            21 => '/NASA Power of Ten Rule #6: Class "AnonymousClass[a-z0-9]+" has 6 properties, but the maximum allowed is 5/',
            42 => '/NASA Power of Ten Rule #6: Public property "publicProp" in class "AnonymousClass[a-z0-9]+" violates data scope restriction/',
            67 => '/NASA Power of Ten Rule #6: Public property "publicProp" in class "AbstractClass" violates data scope restriction/',
        ];

        // Ensure we have the correct number of errors
        Assert::assertCount(count($assertions), $errors, 'Expected number of errors does not match actual errors');

        // We track which assertions have been verified rolling through each error
        $verifiedAssertions = [];

        // Check each error against our expected patterns
        foreach ($errors as $error) {
            $line = $error->getLine();
            $message = $error->getMessage();

            // Check if we have an assertion for this line
            if (isset($assertions[$line])) {
                Assert::assertMatchesRegularExpression(
                    $assertions[$line],
                    $message,
                    "Error message for line $line doesn't match expected pattern"
                );

                // Add the assertion so we can verify it by line number
                $verifiedAssertions[] = $line;
            } else {
                Assert::fail("Unexpected error on line $line: $message");
            }
        }

        // Verify all the assertions we ran match the line numbers reported by PHPStan's analysis
        foreach (array_keys($assertions) as $line) {
            Assert::assertContains($line, $verifiedAssertions, "Expected error on line $line was not found");
        }
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

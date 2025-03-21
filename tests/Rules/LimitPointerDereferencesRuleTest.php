<?php

declare(strict_types=1);

namespace Tests\Rules;

use NASAStan\NASAStanConfiguration;
use NASAStan\Rules\LimitPointerDereferencesRule;
use PhpParser\Node;
use PHPStan\Rules\Rule;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\NASAStanRuleTestCase;

/**
 * @extends NASAStanRuleTestCase<LimitPointerDereferencesRule>
 */
#[CoversClass(LimitPointerDereferencesRule::class)]
final class LimitPointerDereferencesRuleTest extends NASAStanRuleTestCase
{
    private LimitPointerDereferencesRule $rule;

    protected function setUp(): void
    {
        $configuration = new NASAStanConfiguration();
        $this->rule = new LimitPointerDereferencesRule($configuration);
    }

    #[Test]
    public function test_rule_with_default_dereferences(): void
    {
        $this->analyse([__DIR__.'/../Examples/Rule_9/PointerDereferencing.php'], [
            [
                'NASA Power of Ten Rule #9: Method call chains should be limited to 1 dereference(s). Found 2.',
                16,
            ],
            [
                'NASA Power of Ten Rule #9: Property access chains should be limited to 1 dereference(s). Found 2.',
                23,
            ],
            [
                'NASA Power of Ten Rule #9: Array access on property or method result exceeds allowed limit of 1 dereference(s).',
                30,
            ],
            [
                'NASA Power of Ten Rule #9: Variable functions (function pointers) are not allowed.',
                38,
            ],
            [
                'NASA Power of Ten Rule #9: Closures (function pointers) are not allowed.',
                41,
            ],
            [
                'NASA Power of Ten Rule #9: Callable arrays (function pointers) are not allowed.',
                46,
            ],
            [
                'NASA Power of Ten Rule #9: Method call chains should be limited to 1 dereference(s). Found 2.',
                50,
            ],
            [
                'NASA Power of Ten Rule #9: Method calls on static call results exceed allowed limit of 1 dereference(s).',
                50,
            ],
            [
                'NASA Power of Ten Rule #9: Method calls on static call results exceed allowed limit of 1 dereference(s).',
                53,
            ],
        ]);
    }

    #[Test]
    public function test_rule_with_two_allowed_dereferences(): void
    {
        $configurationWithTwo = new NASAStanConfiguration(
            maxAllowedDereferences: 2
        );
        $this->rule = new LimitPointerDereferencesRule($configurationWithTwo);

        $this->analyse([__DIR__.'/../Examples/Rule_9/ConfigurablePointerDereferencing.php'], [
            [
                'NASA Power of Ten Rule #9: Method call chains should be limited to 2 dereference(s). Found 3.',
                16,
            ],
            [
                'NASA Power of Ten Rule #9: Property access chains should be limited to 2 dereference(s). Found 3.',
                23,
            ],
            [
                'NASA Power of Ten Rule #9: Property access chains should be limited to 2 dereference(s). Found 3.',
                30,
            ],
        ]
        );
    }

    #[Test]
    public function test_rule_name(): void
    {
        Assert::assertEquals('NASA Power of Ten Rule #9', $this->rule->getRuleName());
    }

    #[Test]
    public function test_rule_descriptor(): void
    {
        Assert::assertEquals('Limit pointer use to a single dereference, and do not use function pointers.', $this->rule->getRuleDescriptor());
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
            enabledRules: ['rule_1']
        );

        $this->rule = new LimitPointerDereferencesRule($configuration);

        $this->analyse([__DIR__.'/../Examples/Rule_9/PointerDereferencing.php'], []);
    }

    #[Test]
    public function test_enabled_with_bypass_returns_no_errors(): void
    {
        $configuration = new NASAStanConfiguration(
            enabledRules: ['rule_9'],
            exceptRules: ['rule_9']
        );

        $this->rule = new LimitPointerDereferencesRule($configuration);

        $this->analyse([__DIR__.'/../Examples/Rule_9/PointerDereferencing.php'], []);
    }

    protected function getRule(): Rule
    {
        return $this->rule;
    }
}

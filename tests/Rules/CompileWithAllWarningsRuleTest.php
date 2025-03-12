<?php

declare(strict_types=1);

namespace Tests\Rules;

use Nasastan\NasastanConfiguration;
use Nasastan\Rules\CompileWithAllWarningsRule;
use PhpParser\Node;
use PHPStan\Rules\Rule;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\NasastanRuleTestCase;

/**
 * @extends NasastanRuleTestCase<CompileWithAllWarningsRule>
 */
#[CoversClass(CompileWithAllWarningsRule::class)]
final class CompileWithAllWarningsRuleTest extends NasastanRuleTestCase
{
    private readonly CompileWithAllWarningsRule $rule;

    protected function setUp(): void
    {
        $configuration = new NasastanConfiguration();
        $this->rule = new CompileWithAllWarningsRule($configuration);
    }

    #[Test]
    public function test_rule(): void
    {
        $this->analyse([__DIR__.'/../Examples/Rule_10/WarningSuppression.php'], [
            [
                'NASA Power of Ten Rule #10: Missing required declare directive "strict_types".',
                5,
            ],
            [
                'NASA Power of Ten Rule #10: Error suppression operator (@) is not allowed as it hides warnings.',
                14,
            ],
            [
                'NASA Power of Ten Rule #10: Error suppressing function "error_reporting" is not allowed.',
                20,
            ],
            [
                'NASA Power of Ten Rule #10: Error suppressing function "ini_set" is not allowed.',
                21,
            ],
            [
                'NASA Power of Ten Rule #10: Error suppressing function "set_error_handler" is not allowed.',
                22,
            ],
        ]);
    }

    #[Test]
    public function test_rule_name(): void
    {
        Assert::assertEquals('NASA Power of Ten Rule #10', $this->rule->getRuleName());
    }

    #[Test]
    public function test_rule_descriptor(): void
    {
        Assert::assertEquals('Compile with all possible warnings active; all warnings should then be addressed before release of the software.', $this->rule->getRuleDescriptor());
    }

    #[Test]
    public function test_node_type(): void
    {
        Assert::assertEquals(Node::class, $this->rule->getNodeType());
    }

    protected function getRule(): Rule
    {
        return $this->rule;
    }
}

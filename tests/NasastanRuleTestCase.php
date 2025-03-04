<?php

declare(strict_types=1);

namespace Tests;

use Nasastan\NasastanRule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * @template TRule of NasastanRule
 *
 * @extends RuleTestCase<TRule>
 */
abstract class NasastanRuleTestCase extends RuleTestCase
{
    #[Test]
    abstract public function test_rule_name(): void;

    #[Test]
    abstract public function test_rule_descriptor(): void;

    #[Test]
    abstract public function test_node_type(): void;
}

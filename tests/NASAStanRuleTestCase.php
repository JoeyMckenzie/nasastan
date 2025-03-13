<?php

declare(strict_types=1);

namespace Tests;

use NASAStan\NASAStanRule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * @template TRule of NASAStanRule
 *
 * @extends RuleTestCase<TRule>
 */
abstract class NASAStanRuleTestCase extends RuleTestCase
{
    #[Test]
    abstract public function test_rule_name(): void;

    #[Test]
    abstract public function test_rule_descriptor(): void;

    #[Test]
    abstract public function test_node_type(): void;

    #[Test]
    abstract public function test_not_enabled_returns_no_errors(): void;

    #[Test]
    abstract public function test_enabled_with_bypass_returns_no_errors(): void;
}

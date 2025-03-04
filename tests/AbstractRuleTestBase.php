<?php

declare(strict_types=1);

namespace Tests;

use Nasastan\NasastanRule;
use PHPStan\Testing\RuleTestCase;

/**
 * @template TRule of NasastanRule
 */
abstract class AbstractRuleTestBase extends RuleTestCase
{
    abstract public function test_rule_name(): void;

    abstract public function test_rule_descriptor(): void;

    abstract public function test_node_type(): void;
}

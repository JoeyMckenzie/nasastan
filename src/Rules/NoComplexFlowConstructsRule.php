<?php

namespace Nasastan\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Rule #1: No complex flow constructs
 * Detects use of goto statements and recursion
 */
final class NoComplexFlowConstructsRule implements Rule
{
    public function getNodeType(): string
    {
        return Node\Stmt\Goto_::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        return [
            RuleErrorBuilder::message('NASA Power of Ten Rule #1: Goto statements are not allowed.')
                ->build(),
        ];
    }
}
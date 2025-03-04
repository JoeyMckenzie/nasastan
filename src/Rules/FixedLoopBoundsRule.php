<?php

namespace Nasastan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\While_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Rule #2: All loops must have fixed bounds
 * Checks that all loops have a condition with a fixed upper bound
 */
final class FixedLoopBoundsRule implements Rule
{
    /**
     * @return class-string
     */
    public function getNodeType(): string
    {
        return Node::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if ($node instanceof While_ || $node instanceof Do_) {
            // Simplified check - a full implementation would need to analyze the condition
            // to ensure it has a fixed bound
            return [
                RuleErrorBuilder::message(
                    'NASA Power of Ten Rule #2: All loops must have fixed bounds. Use for loops with explicit counters.'
                )->build(),
            ];
        }

        if ($node instanceof For_) {
            // For loops are generally acceptable if they have proper init, cond, and loop parts
            if (count($node->init) === 0 || $node->cond === [] || count($node->loop) === 0) {
                return [
                    RuleErrorBuilder::message(
                        'NASA Power of Ten Rule #2: For loops must have initialization, condition, and increment parts.'
                    )->build(),
                ];
            }
        }

        if ($node instanceof Foreach_) {
            // For foreach loops, we need to check that the array being iterated has a fixed size
            // This is a simplified check
            return [
                RuleErrorBuilder::message(
                    'NASA Power of Ten Rule #2: Foreach loops must iterate over arrays with fixed size. Consider using for loops with explicit counters.'
                )->build(),
            ];
        }

        return [];
    }
}
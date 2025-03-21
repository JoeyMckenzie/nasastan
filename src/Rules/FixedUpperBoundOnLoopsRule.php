<?php

declare(strict_types=1);

namespace NASAStan\Rules;

use Exception;
use NASAStan\NASAStanConfiguration;
use NASAStan\NASAStanException;
use NASAStan\NASAStanRule;
use NASAStan\Rules\Concerns\HasRuleEnablement;
use Override;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\While_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\ObjectType;

/**
 * Rule #2: All loops must have fixed bounds. This prevents runaway code.
 *
 * @implements NASAStanRule<Node\Stmt>
 */
final readonly class FixedUpperBoundOnLoopsRule implements NASAStanRule
{
    use HasRuleEnablement;

    public function __construct(
        private NASAStanConfiguration $configuration
    ) {
        //
    }

    public function getNodeType(): string
    {
        return Node\Stmt::class;
    }

    /**
     * @throws NASAStanException
     */
    #[Override]
    public function processNode(Node $node, Scope $scope): array
    {
        if (! $this->enabled('rule_2')) {
            return [];
        }

        try {
            if ($node instanceof For_) {
                return $this->checkForLoop($node, $scope);
            }

            if ($node instanceof While_ || $node instanceof Do_) {
                return $this->checkWhileLoop($node);
            }

            if ($node instanceof Foreach_) {
                return $this->checkForeachLoop($node, $scope);
            }
        } catch (Exception $e) {
            throw NASAStanException::from($this->getRuleName(), $e);
        }

        return [];
    }

    public function getRuleName(): string
    {
        return 'NASA Power of Ten Rule #2';
    }

    public function getRuleDescriptor(): string
    {
        return 'All loops must have fixed bounds. This prevents runaway code.';
    }

    /**
     * Checks for loops for upper-bound conditions.
     *
     * @return RuleError[]
     *
     * @throws ShouldNotHappenException
     */
    private function checkForLoop(For_ $node, Scope $scope): array
    {
        // For loops should have a condition and increment
        if (count($node->cond) === 0 || count($node->loop) === 0) {
            return [
                RuleErrorBuilder::message(sprintf(
                    '%s: For loop must have a condition and increment to ensure fixed bounds.',
                    $this->getRuleName()
                ))->build(),
            ];
        }

        // Check if the condition has a fixed upper bound, in which case no further analysis is needed
        if (array_any($node->cond, fn (Expr $loopCondition): bool => $this->hasFixedUpperBound($loopCondition, $scope))) {
            return [];
        }

        // We didn't detect a fixed upper bound, so assume we've got some runaway code
        return [
            RuleErrorBuilder::message(sprintf(
                '%s: For loop must have a fixed upper bound to prevent runaway code.',
                $this->getRuleName()
            ))->build(),
        ];
    }

    /**
     * Checks if an expression is a fixed upper bound.
     */
    private function hasFixedUpperBound(Expr $loopCondition, Scope $scope): bool
    {
        // Check for comparison operators in the loop condition
        if ($loopCondition instanceof Expr\BinaryOp\Smaller || $loopCondition instanceof Expr\BinaryOp\SmallerOrEqual) {
            $rightType = $scope->getType($loopCondition->right);

            // Check for the simple case of index < configured upper limit where the configured upper limit is a constant
            if ($rightType instanceof ConstantIntegerType) {
                return $rightType->getValue() <= $this->configuration->maxAllowedIterations;
            }

            // Next, check for constant expressions like index < COUNT or index < LIMIT
            if ($rightType->isConstantScalarValue()->yes()) {
                $rightValue = $rightType->getConstantScalarValues()[0];

                if (is_int($rightValue) && $rightValue <= $this->configuration->maxAllowedIterations) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Checks while loops for concrete upper bounds.
     *
     * @return RuleError[]
     *
     * @throws ShouldNotHappenException
     */
    private function checkWhileLoop(While_|Do_ $node): array
    {
        // For simple detection, first we'll check for while(true) or do-while(true) conditions
        if ($this->isAlwaysTrue($node->cond)) {
            return [
                RuleErrorBuilder::message(sprintf(
                    '%s: While/do-while loop with condition "true" has no upper bound.',
                    $this->getRuleName()
                ))->build(),
            ];
        }

        // Check if we can determine the loop has a counter with a fixed bound
        if ($this->hasLoopCounterWithBound()) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                '%s: While/do-while loop must have a verifiable fixed upper bound to prevent runaway code.',
                $this->getRuleName()
            ))->build(),
        ];
    }

    /**
     * Check if a node condition is always true
     */
    private function isAlwaysTrue(Expr $cond): bool
    {
        return $cond instanceof Expr\ConstFetch && $cond->name->toString() === 'true';
    }

    /**
     * Analyzes if a while loop has a counter with a fixed bound.
     */
    private function hasLoopCounterWithBound(): bool
    {
        // TODO: Gonna need some help with this. This would require more sophisticated analysis to reliably detect
        // counter variables and their bounds in a while loop to handle patterns like:
        //   $i = 0; while ($i < 100) { $i++; }
        // Currently we'll return false to be conservative
        return false;
    }

    /**
     * Checks a foreach loop to verify an upper bound can be detected.
     *
     * @return RuleError[]
     *
     * @throws ShouldNotHappenException
     */
    private function checkForeachLoop(Foreach_ $node, Scope $scope): array
    {
        $exprType = $scope->getType($node->expr);

        // Arrays with known size are inherently bounded, so we should be able to determine their upper bound
        $constantArrays = $exprType->getConstantArrays();

        // Roll through the arrays, checking each arrays size against the configured limit
        foreach ($constantArrays as $value) {
            $arraySize = count($value->getKeyTypes());

            if ($arraySize > $this->configuration->maxAllowedIterations) {
                return [
                    RuleErrorBuilder::message(sprintf(
                        '%s: Foreach loop iterates over %d items, which exceeds the configured maximum of %d iterations.',
                        $this->getRuleName(),
                        $arraySize,
                        $this->configuration->maxAllowedIterations
                    ))->build(),
                ];
            }
        }

        // Check if it's a countable object or array type
        $objectIsCountable = new ObjectType('Countable')->isSuperTypeOf($exprType)->yes();
        $expressionTypeIsArray = $exprType->isArray()->yes();

        if ($objectIsCountable || $expressionTypeIsArray) {
            // We can't statically guarantee the size, but we'll likely assume it's bounded
            return [];
        }

        // For generators or other iterables, we can't guarantee a bound
        return [
            RuleErrorBuilder::message(sprintf(
                '%s: Foreach loop must iterate over a countable collection with a verifiable size bound.',
                $this->getRuleName()
            ))->build(),
        ];
    }
}

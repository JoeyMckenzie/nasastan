<?php

declare(strict_types=1);

namespace Nasastan\Rules;

use Nasastan\NasastanConfiguration;
use Nasastan\NasastanRule;
use Nasastan\Rules\Concerns\HasNodeClassType;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Throw_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\NodeFinder;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Rule #5: Use a minimum of two runtime assertions per function.
 *
 * @implements NasastanRule<Node>
 */
final class MinimumAssertionsPerFunctionRule implements NasastanRule
{
    use HasNodeClassType;

    /**
     * @var string[]
     */
    private array $assertionFunctions;

    /**
     * @var string[]
     */
    private array $assertionMethods;

    /**
     * @var string[]
     */
    private array $exceptionThrowingFunctions;

    private int $minimumAssertionsRequired;

    public function __construct(NasastanConfiguration $configuration)
    {
        $this->assertionFunctions = $configuration->assertionFunctions;
        $this->assertionMethods = $configuration->assertionMethods;
        $this->exceptionThrowingFunctions = $configuration->exceptionThrowingFunctions;
        $this->minimumAssertionsRequired = $configuration->minimumAssertionsRequired;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        // Skip if this is an interface or abstract method without a body
        if ($node instanceof ClassMethod && ($node->isAbstract() || $node->stmts === null)) {
            return [];
        }

        // Skip magic methods
        if ($node instanceof ClassMethod && mb_strpos($node->name->toString(), '__') === 0) {
            return [];
        }

        if ($node instanceof ClassMethod || $node instanceof Function_) {
            $functionName = $node->name->toString();

            // Count assertions in the function body
            $assertionsCount = $this->countAssertionsInNode($node);

            if ($assertionsCount < $this->minimumAssertionsRequired) {
                return [
                    RuleErrorBuilder::message(
                        sprintf(
                            'NASA Power of Ten Rule #5: Function "%s" contains %d assertions, but at least %d are required.',
                            $functionName,
                            $assertionsCount,
                            $this->minimumAssertionsRequired
                        )
                    )->build(),
                ];
            }
        }

        return [];
    }

    public function getRuleName(): string
    {
        return 'NASA Power of Ten Rule #5';
    }

    public function getRuleDescriptor(): string
    {
        return 'Use a minimum of two runtime assertions per function.';
    }

    /**
     * Count the number of assertions in a node.
     */
    private function countAssertionsInNode(Node $node): int
    {
        $nodeFinder = new NodeFinder();
        $assertionCount = 0;

        // Find all function calls that might be assertions
        $funcCalls = $nodeFinder->findInstanceOf($node, FuncCall::class);
        foreach ($funcCalls as $funcCall) {
            if ($funcCall->name instanceof Node\Name) {
                $functionName = $funcCall->name->toString();
                if (in_array($functionName, $this->assertionFunctions, true)) {
                    $assertionCount++;
                }
            }
        }

        // Find all method calls that might be assertions
        $methodCalls = $nodeFinder->findInstanceOf($node, MethodCall::class);
        foreach ($methodCalls as $methodCall) {
            if ($methodCall->name instanceof Node\Identifier) {
                $methodName = $methodCall->name->toString();
                if (in_array($methodName, $this->assertionMethods, true)) {
                    $assertionCount++;
                }
            }
        }

        // Find all static method calls that might be assertions
        $staticCalls = $nodeFinder->findInstanceOf($node, StaticCall::class);
        foreach ($staticCalls as $staticCall) {
            if ($staticCall->name instanceof Node\Identifier) {
                $methodName = $staticCall->name->toString();
                if (in_array($methodName, $this->assertionMethods, true)) {
                    $assertionCount++;
                }
            }
        }

        // Find if statements with exception-throwing calls or throw statements,
        // which act as assertions
        $ifStatements = $nodeFinder->findInstanceOf($node, Node\Stmt\If_::class);
        foreach ($ifStatements as $ifStatement) {
            // Look for throw statements within if blocks
            foreach ($ifStatement->stmts as $stmt) {
                // After some debugging, there's definitely an expr property associated to the statement
                if ($stmt->expr instanceof Throw_) { // @phpstan-ignore-line
                    $assertionCount++;
                    break;
                }
            }

            // Also look for exception-throwing function calls within if statements
            $ifFuncCalls = $nodeFinder->findInstanceOf($ifStatement->stmts, FuncCall::class);
            foreach ($ifFuncCalls as $funcCall) {
                if ($funcCall->name instanceof Node\Name) {
                    $functionName = $funcCall->name->toString();
                    if (in_array($functionName, $this->exceptionThrowingFunctions, true)) {
                        $assertionCount++;

                        // Only count one per if statement
                        break;
                    }
                }
            }
        }

        // Find ternary expressions with exception-throwing
        $ternaries = $nodeFinder->findInstanceOf($node, Node\Expr\Ternary::class);
        foreach ($ternaries as $ternary) {
            $throwCalls = $nodeFinder->findInstanceOf($ternary, FuncCall::class);
            foreach ($throwCalls as $funcCall) {
                if ($funcCall->name instanceof Node\Name) {
                    $functionName = $funcCall->name->toString();
                    if (in_array($functionName, $this->exceptionThrowingFunctions, true)) {
                        $assertionCount++;

                        // Only count one per ternary
                        break;
                    }
                }
            }
        }

        return $assertionCount;
    }
}

<?php

declare(strict_types=1);

namespace NASAStan\Rules;

use NASAStan\NASAStanConfiguration;
use NASAStan\NASAStanException;
use NASAStan\NASAStanRule;
use NASAStan\Rules\Concerns\HasNodeClassType;
use NASAStan\Rules\Concerns\HasRuleEnablement;
use Override;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * Rule #1: No complex flow constructs. This rule detects use of goto statements and recursion.
 *
 * @implements NASAStanRule<Node>
 */
final readonly class NoComplexFlowConstructsRule implements NASAStanRule
{
    use HasNodeClassType, HasRuleEnablement;

    public function __construct(
        private NASAStanConfiguration $configuration,
    ) {
        //
    }

    /**
     * @throws NASAStanException
     */
    #[Override]
    public function processNode(Node $node, Scope $scope): array
    {
        if (! $this->enabled('rule_1')) {
            return [];
        }

        // First, we'll check for goto statements - pretty simple, outlaw all gotos
        if ($node instanceof Node\Stmt\Goto_) {
            try {
                return [
                    RuleErrorBuilder::message(sprintf('%s: Goto statements are not allowed.', $this->getRuleName()))
                        ->build(),
                ];
            } catch (ShouldNotHappenException $e) {
                throw NASAStanException::from($this->getRuleName(), $e);
            }
        }

        // Next, check for direct recursion in function calls
        if ($node instanceof FuncCall && $node->name instanceof Name) {
            $functionName = $node->name->toString();
            $currentFunction = $scope->getFunctionName();

            // Get the short name of the current function without the inclusion of the namespace
            $currentFunctionShortName = $currentFunction;

            if (str_contains((string) $currentFunction, '\\')) {
                $parts = explode('\\', (string) $currentFunction);
                $currentFunctionShortName = end($parts);
            }

            // We've detected a function calling itself
            if ($functionName === $currentFunctionShortName) {
                try {
                    return [
                        RuleErrorBuilder::message(sprintf('%s: Recursive function calls are not allowed.', $this->getRuleName()))
                            ->build(),
                    ];
                } catch (ShouldNotHappenException $e) {
                    throw NASAStanException::from($this->getRuleName(), $e);
                }
            }
        }

        // Similarly, check for direct recursion in method calls
        if ($node instanceof Node\Expr\MethodCall) {
            $methodName = $node->name->name ?? null; // @phpstan-ignore-line
            $currentClass = $scope->getClassReflection();
            $currentMethod = $scope->getFunctionName();
            $currentMethodShortName = $currentMethod;

            // Check if we're within a static/namespace prefixed method
            if (str_contains((string) $currentMethod, '::')) {
                $parts = explode('::', (string) $currentMethod);
                $currentMethodShortName = end($parts);
            }

            // We've detected a method class method calling itself
            if ($currentClass instanceof ClassReflection && $currentMethod !== null && $methodName === $currentMethodShortName) {
                try {
                    return [
                        RuleErrorBuilder::message(sprintf('%s: Recursive method calls are not allowed.', $this->getRuleName()))
                            ->build(),
                    ];
                } catch (ShouldNotHappenException $e) {
                    throw NASAStanException::from($this->getRuleName(), $e);
                }
            }
        }

        // Lastly, check for direct recursion in static method calls
        if ($node instanceof StaticCall && $node->name instanceof Node\Identifier) {
            $methodName = $node->name->name;
            $currentClass = $scope->getClassReflection();
            $currentMethod = $scope->getFunctionName();
            $currentMethodShortName = $currentMethod;

            // Check if we're within a static/namespace prefixed method
            if (str_contains((string) $currentMethod, '::')) {
                $parts = explode('::', (string) $currentMethod);
                $currentMethodShortName = end($parts);
            }

            // Next, we need to determine if we're within a method calling itself
            if ($currentClass instanceof ClassReflection && $currentMethod !== null && $methodName === $currentMethodShortName) {
                $calledClass = $node->class instanceof Name
                    ? $node->class->toString()
                    : null;

                // We've detected a static method calling itself
                if ($calledClass === 'self' || $calledClass === 'static' || $calledClass === $currentClass->getName()) {
                    try {
                        return [
                            RuleErrorBuilder::message(sprintf('%s: Recursive static method calls are not allowed.', $this->getRuleName()))
                                ->build(),
                        ];
                    } catch (ShouldNotHappenException $e) {
                        throw NASAStanException::from($this->getRuleName(), $e);
                    }
                }
            }
        }

        return [];
    }

    public function getRuleName(): string
    {
        return 'NASA Power of Ten Rule #1';
    }

    public function getRuleDescriptor(): string
    {
        return 'Avoid complex flow constructs, such as goto and recursion.';
    }
}

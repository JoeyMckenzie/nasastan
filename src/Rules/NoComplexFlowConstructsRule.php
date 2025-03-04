<?php

declare(strict_types=1);

namespace Nasastan\Rules;

use Nasastan\NasastanException;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Rule #1: No complex flow constructs. This rule detects use of goto statements and recursion.
 */
final class NoComplexFlowConstructsRule implements Rule
{
    public function getRuleName(): string
    {
        return 'NASA Power of Ten Rule #1';
    }

    public function getRuleDescriptor(): string
    {
        return 'Avoid complex flow constructs, such as goto and recursion.';
    }

    public function getNodeType(): string
    {
        // We need to handle multiple node types
        return Node::class;
    }

    /**
     * @throws NasastanException
     */
    public function processNode(Node $node, Scope $scope): array
    {
        // Check for goto statements
        if ($node instanceof Node\Stmt\Goto_) {
            return [
                RuleErrorBuilder::message(sprintf('%s: Goto statements are not allowed.', $this->getRuleName()))
                    ->build(),
            ];
        }
        // Check for direct recursion in function calls
        if ($node instanceof FuncCall && $node->name instanceof Name) {
            $functionName = $node->name->toString();
            $currentFunction = $this->getCurrentFunctionName($scope);

            // Get the short name of the current function (without namespace)
            $currentFunctionShortName = $currentFunction;
            if (str_contains((string) $currentFunction, '\\')) {
                $parts = explode('\\', (string) $currentFunction);
                $currentFunctionShortName = end($parts);
            }

            if ($functionName === $currentFunctionShortName) {
                return [
                    RuleErrorBuilder::message(sprintf('%s: Recursive function calls are not allowed.', $this->getRuleName()))
                        ->build(),
                ];
            }
        }
        // Check for direct recursion in method calls
        if ($node instanceof Node\Expr\MethodCall) {
            $methodName = $node->name->name ?? null;
            $currentClass = $scope->getClassReflection();
            $currentMethod = $scope->getFunctionName();

            // Get the short name of the current method (without namespace)
            $currentMethodShortName = $currentMethod;
            if (str_contains((string) $currentMethod, '::')) {
                $parts = explode('::', (string) $currentMethod);
                $currentMethodShortName = end($parts);
            }

            if ($currentClass instanceof \PHPStan\Reflection\ClassReflection && $currentMethod !== null && $methodName === $currentMethodShortName) {
                return [
                    RuleErrorBuilder::message(sprintf('%s: Recursive method calls are not allowed.', $this->getRuleName()))
                        ->build(),
                ];
            }
        }
        // Check for direct recursion in static method calls
        if ($node instanceof StaticCall && $node->name instanceof Node\Identifier) {
            $methodName = $node->name->name;
            $currentClass = $scope->getClassReflection();
            $currentMethod = $scope->getFunctionName();

            // Get the short name of the current method (without namespace)
            $currentMethodShortName = $currentMethod;
            if (str_contains((string) $currentMethod, '::')) {
                $parts = explode('::', (string) $currentMethod);
                $currentMethodShortName = end($parts);
            }

            if ($currentClass instanceof \PHPStan\Reflection\ClassReflection && $currentMethod !== null && $methodName === $currentMethodShortName) {
                $calledClass = null;
                if ($node->class instanceof Name) {
                    $calledClass = $node->class->toString();
                }

                if ($calledClass === 'self' || $calledClass === 'static' || $calledClass === $currentClass->getName()) {
                    return [
                        RuleErrorBuilder::message(sprintf('%s: Recursive static method calls are not allowed.', $this->getRuleName()))
                            ->build(),
                    ];
                }
            }
        }

        return [];
    }

    /**
     * Get the current function name from the scope
     */
    private function getCurrentFunctionName(Scope $scope): ?string
    {
        return $scope->getFunctionName();
    }
}

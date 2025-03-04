<?php

namespace Nasastan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Rule #1b: No recursion allowed
 * This is a simplified check - a complete check would require call graph analysis
 */
final class NoRecursionRule implements Rule
{
    public function getNodeType(): string
    {
        return ClassMethod::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof ClassMethod) {
            return [];
        }

        $methodName = $node->name->toString();
        $className = $scope->getClassReflection()?->getName();

        if ($className === null) {
            return [];
        }

        foreach ($node->stmts ?? [] as $stmt) {
            $stmtMethodCalls = $this->findMethodCalls($stmt);
            foreach ($stmtMethodCalls as $call) {
                if ($call->name->toString() === $methodName) {
                    return [
                        RuleErrorBuilder::message(
                            sprintf(
                                'NASA Power of Ten Rule #1: Method "%s" contains recursive calls to itself.',
                                $methodName
                            )
                        )->build(),
                    ];
                }
            }
        }

        return [];
    }

    /**
     * Helper function to find method calls in a statement
     *
     * @param Node $node
     * @return MethodCall[]
     */
    private function findMethodCalls(Node $node): array
    {
        $calls = [];

        if ($node instanceof MethodCall) {
            $calls[] = $node;
        }

        foreach ($node->getSubNodeNames() as $subNodeName) {
            $subNode = $node->$subNodeName;

            if ($subNode instanceof Node) {
                $calls = array_merge($calls, $this->findMethodCalls($subNode));
            } elseif (is_array($subNode)) {
                foreach ($subNode as $item) {
                    if ($item instanceof Node) {
                        $calls = array_merge($calls, $this->findMethodCalls($item));
                    }
                }
            }
        }

        return $calls;
    }
}
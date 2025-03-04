<?php

namespace Nasastan\Rules;

use Nasastan\NasastanException;
use Nasastan\NasastanRule;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\VoidType;

/**
 * Rule #8: The return value of non-void functions must be checked
 */
final class CheckReturnValueRule implements NasastanRule
{
    /**
     * @var array<string> Functions whose return values are allowed to be ignored
     */
    private array $ignoredFunctions;

    /**
     * @var bool Whether to allow fluent interface calls
     */
    private bool $allowFluentInterfaces;

    public function __construct(
        array $ignoredFunctions = [],
        bool $allowFluentInterfaces = true
    ) {
        $this->ignoredFunctions = array_merge(
            [
                // Functions commonly used for side effects
                'array_push',
                'array_unshift',
                'session_start',
                'header',
                'setcookie',
                'error_log',
                'trigger_error',
            ],
            $ignoredFunctions
        );
        $this->allowFluentInterfaces = $allowFluentInterfaces;
    }

    public array $ruleDescriptors {
        get {
            return [
                'NASA Power of Ten Rule #8'
            ];
        }
    }

    public function getNodeType(): string
    {
        return Expression::class;
    }

    /**
     * @throws NasastanException
     */
    public function processNode(Node $node, Scope $scope): array
    {
        // We're now checking Expression nodes to see if they contain unused function calls
        if (!$node instanceof Expression) {
            return [];
        }

        // Get the expression within the Expression statement
        $expr = $node->expr;

        // Check if the expression is a function/method call
        if (!($expr instanceof FuncCall) &&
            !($expr instanceof MethodCall) &&
            !($expr instanceof StaticCall)) {
            return [];
        }

        // Check for fluent interface (method chaining)
        if ($this->allowFluentInterfaces && $this->isPartOfFluentInterface($expr)) {
            return [];
        }

        // Get the function name
        $functionName = $this->getFunctionName($expr);

        // Skip if this function is in our ignored list
        if (in_array($functionName, $this->ignoredFunctions, true)) {
            return [];
        }

        // Get the return type
        $returnType = $scope->getType($expr);

        // If the function returns void, no need to check return value
        if ($returnType instanceof VoidType) {
            return [];
        }

        // At this point, we have a non-void function call whose return value is not used
        if (empty($functionName)) {
            return [];
        }

        try {
            return [
                RuleErrorBuilder::message(
                    sprintf(
                        '%s: Return value of function "%s" is not checked.',
                        $this->ruleDescriptors[0],
                        $functionName
                    )
                )->build(),
            ];
        } catch (ShouldNotHappenException $e) {
            throw new NasastanException($this->ruleDescriptors[0], $e);
        }
    }

    /**
     * Check if this method call is part of a fluent interface (method chaining)
     */
    private function isPartOfFluentInterface(Node $node): bool
    {
        // Only apply to method calls, not function calls or static calls
        if (!$node instanceof MethodCall) {
            return false;
        }

        // Check if this method call's var is itself a method call
        // This would indicate method chaining like $obj->method1()->method2()
        return $node->var instanceof MethodCall || $node->var instanceof StaticCall;
    }

    /**
     * Get the function/method name from a call expression
     */
    private function getFunctionName(Node $node): string
    {
        if ($node instanceof FuncCall && $node->name instanceof Node\Name) {
            return $node->name->toString();
        }

        if ($node instanceof MethodCall &&
            $node->name instanceof Node\Identifier) {
            // For method calls, we could include the variable name but that's complex
            // Just using the method name is often sufficient
            return $node->name->toString();
        }

        if ($node instanceof StaticCall &&
            $node->name instanceof Node\Identifier &&
            $node->class instanceof Node\Name) {
            return $node->class->toString() . '::' . $node->name->toString();
        }

        return '';
    }
}
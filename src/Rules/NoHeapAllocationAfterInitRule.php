<?php

declare(strict_types=1);

namespace Nasastan\Rules;

use Nasastan\NasastanConfiguration;
use Nasastan\NasastanRule;
use Nasastan\Rules\Concerns\HasNodeClassType;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;

/**
 * Rule #3: Avoid heap memory allocation after initialization.
 *
 * @implements NasastanRule<Node>
 */
final class NoHeapAllocationAfterInitRule implements NasastanRule
{
    use HasNodeClassType;

    /** @var array<string> */
    private array $resourceAllocationFunctions;

    /** @var array<string> */
    private array $allowedInitMethods;

    public function __construct(NasastanConfiguration $configuration)
    {
        $this->allowedInitMethods = $configuration->allowedInitMethods;
        $this->resourceAllocationFunctions = $configuration->resourceAllocationFunctions;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        // Skip if we're in an initialization method
        if ($this->isApprovedInitializationMethod($scope)) {
            return [];
        }

        // Check for 'new' expressions
        if ($node instanceof New_) {
            return [
                RuleErrorBuilder::message(
                    'NASA Power of Ten Rule #3: Object instantiation is not allowed after initialization.'
                )->build(),
            ];
        }

        // Check for dynamic array creation with items
        if ($node instanceof Array_ && count($node->items) > 0) {
            return [
                RuleErrorBuilder::message(
                    'NASA Power of Ten Rule #3: Dynamic array creation is not allowed after initialization.'
                )->build(),
            ];
        }

        // Check for resource allocation functions
        if ($node instanceof FuncCall && $node->name instanceof Name) {
            $functionName = $node->name->toString();
            if (in_array($functionName, $this->resourceAllocationFunctions, true)) {
                return [
                    RuleErrorBuilder::message(
                        sprintf('NASA Power of Ten Rule #3: Resource allocation function "%s" is not allowed after initialization.', $functionName)
                    )->build(),
                ];
            }
        }

        // Check for dynamic container method calls that allocate memory
        if ($node instanceof MethodCall && $node->name instanceof Node\Identifier) {
            $methodName = $node->name->toString();

            if ($this->isDynamicContainerMethod($methodName, $scope, $node)) {
                return [
                    RuleErrorBuilder::message(
                        sprintf('NASA Power of Ten Rule #3: Container method "%s" that allocates memory is not allowed after initialization.', $methodName)
                    )->build(),
                ];
            }
        }

        return [];
    }

    public function getRuleName(): string
    {
        return 'NASA Power of Ten Rule #3';
    }

    public function getRuleDescriptor(): string
    {
        return 'Avoid heap memory allocation after initialization.';
    }

    private function isApprovedInitializationMethod(Scope $scope): bool
    {
        $function = $scope->getFunction();
        if (! $function instanceof \PHPStan\Reflection\Php\PhpFunctionFromParserNodeReflection) {
            return false;
        }

        $functionName = $function->getName();

        return in_array($functionName, $this->allowedInitMethods, true);
    }

    /**
     * Check if a method call is on a dynamic container and allocates memory
     */
    private function isDynamicContainerMethod(string $methodName, Scope $scope, MethodCall $node): bool
    {
        // List of container methods that allocate memory
        $containerMethods = [
            'add',
            'push',
            'append',
            'insert',
            'put',
            'set',
            // Add more container methods that allocate memory
        ];

        if (! in_array($methodName, $containerMethods, true)) {
            return false;
        }

        // Check if the called object is a container type
        $calledOnType = $scope->getType($node->var);

        $containerClasses = [
            'SplDoublyLinkedList',
            'SplStack',
            'SplQueue',
            'ArrayObject',
            // Add more container classes as needed
        ];

        return array_any($containerClasses, fn ($containerClass): bool => $calledOnType->accepts(new ObjectType($containerClass), true)->yes());
    }
}

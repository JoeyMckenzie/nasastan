<?php

declare(strict_types=1);

namespace Nasastan\Rules;

use Nasastan\NasastanConfiguration;
use Nasastan\NasastanException;
use Nasastan\NasastanRule;
use Nasastan\Rules\Concerns\HasNodeClassType;
use Override;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\Php\PhpFunctionFromParserNodeReflection;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;
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

    /**
     * @throws NasastanException
     * @throws ShouldNotHappenException
     */
    #[Override]
    public function processNode(Node $node, Scope $scope): array
    {
        // If we're in an initialization method, bail out
        if ($this->isApprovedInitializationMethod($scope)) {
            return [];
        }

        // Check for 'new' expressions
        if ($node instanceof New_) {
            try {
                return [
                    RuleErrorBuilder::message(
                        'NASA Power of Ten Rule #3: Object instantiation is not allowed after initialization.'
                    )->build(),
                ];
            } catch (ShouldNotHappenException $e) {
                throw NasastanException::from($this->getRuleName(), $e);
            }
        }

        // Check for dynamic array creation with items
        if ($node instanceof Array_ && count($node->items) > 0) {
            try {
                return [
                    RuleErrorBuilder::message(
                        'NASA Power of Ten Rule #3: Dynamic array creation is not allowed after initialization.'
                    )->build(),
                ];
            } catch (ShouldNotHappenException $e) {
                throw NasastanException::from($this->getRuleName(), $e);
            }
        }

        // Check for resource allocation functions
        if ($node instanceof FuncCall && $node->name instanceof Name) {
            $functionName = $node->name->toString();
            if (in_array($functionName, $this->resourceAllocationFunctions, true)) {
                try {
                    return [
                        RuleErrorBuilder::message(
                            sprintf('NASA Power of Ten Rule #3: Resource allocation function "%s" is not allowed after initialization.', $functionName)
                        )->build(),
                    ];
                } catch (ShouldNotHappenException $e) {
                    throw NasastanException::from($this->getRuleName(), $e);
                }
            }
        }

        // Check for dynamic container method calls that allocate memory
        if ($node instanceof MethodCall && $node->name instanceof Node\Identifier) {
            $methodName = $node->name->toString();

            if ($this->isDynamicContainerMethod($methodName, $scope, $node)) {
                try {
                    return [
                        RuleErrorBuilder::message(
                            sprintf('NASA Power of Ten Rule #3: Container method "%s" that allocates memory is not allowed after initialization.', $methodName)
                        )->build(),
                    ];
                } catch (ShouldNotHappenException $e) {
                    throw NasastanException::from($this->getRuleName(), $e);
                }
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

    /**
     * Determines if the current code scope is a whitelisted initialization method based on configuration.
     *
     * @throws ShouldNotHappenException
     */
    private function isApprovedInitializationMethod(Scope $scope): bool
    {
        $function = $scope->getFunction();

        // Claude may have helped figure this condition out...
        if (! $function instanceof PhpFunctionFromParserNodeReflection) {
            return false;
        }

        $functionName = $function->getName();

        return in_array($functionName, $this->allowedInitMethods, true);
    }

    /**
     * Checks if a method call is on a dynamic container and allocates memory.
     */
    private function isDynamicContainerMethod(string $methodName, Scope $scope, MethodCall $node): bool
    {
        // List of container methods that allocate memory
        // TODO: Probably need to put this in configuration
        $containerMethods = [
            'add',
            'push',
            'append',
            'insert',
            'put',
            'set',
        ];

        if (! in_array($methodName, $containerMethods, true)) {
            return false;
        }

        // Check if the called object is a container type
        $calledOnType = $scope->getType($node->var);

        // TODO: Probably need to put this in configuration
        $containerClasses = [
            'SplDoublyLinkedList',
            'SplStack',
            'SplQueue',
            'ArrayObject',
        ];

        return array_any($containerClasses, fn (string $containerClass): bool => $calledOnType->accepts(new ObjectType($containerClass), true)->yes());
    }
}

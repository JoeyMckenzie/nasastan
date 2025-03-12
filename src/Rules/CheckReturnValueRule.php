<?php

declare(strict_types=1);

namespace Nasastan\Rules;

use Nasastan\NasastanConfiguration;
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
use PHPStan\Type\MixedType;

/**
 * Rule #7: Check the return value of all non-void functions, or cast to void to indicate the return value is useless.
 *
 * @implements NasastanRule<Node\Stmt\Expression>
 */
final readonly class CheckReturnValueRule implements NasastanRule
{
    /** @var string[] */
    private array $ignoredFunctions;

    public function __construct(NasastanConfiguration $configuration)
    {
        $this->ignoredFunctions = $configuration->ignoreReturnValueForFunctions;
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
        $expr = $node->expr;

        // We only need to check function/method calls, skip lambdas and the like for now
        if (! $expr instanceof FuncCall && ! $expr instanceof MethodCall && ! $expr instanceof StaticCall) {
            return [];
        }

        // We'll also skip analysis if it's been explicitly marked to ignore return value
        if ($this->hasIgnoreReturnValueAnnotation($node)) {
            return [];
        }

        // For function calls, we'll check if it's in the ignored list and bail out if it's whitelisted
        if ($expr instanceof FuncCall && $this->isIgnoredFunction($expr)) {
            return [];
        }

        $returnType = $scope->getType($expr);

        // If it's void, no need to check further as we've satisfied the Power Of Ten rule
        if ($returnType->isVoid()->yes()) {
            return [];
        }

        // If the return type is null or mixed, we can't reliably determine if it needs to be checked
        if ($returnType->isNull()->yes() || $returnType instanceof MixedType) {
            return [];
        }

        // If we got here, we have a non-void return value that isn't being used
        try {
            return [
                RuleErrorBuilder::message(
                    sprintf(
                        'NASA Power of Ten Rule #6: Return value of %s is not used. Either use the return value or add a @ignoreReturnValue annotation.',
                        $this->getFunctionName($expr, $scope)
                    )
                )->build(),
            ];
        } catch (ShouldNotHappenException $e) {
            throw NasastanException::from($this->getRuleName(), $e);
        }
    }

    public function getRuleName(): string
    {
        return 'NASA Power of Ten Rule #6';
    }

    public function getRuleDescriptor(): string
    {
        return 'Check the return value of all non-void functions, or cast to void to indicate the return value is useless.';
    }

    /**
     * Checks if the expression has an annotation to explicitly ignore the return value.
     */
    private function hasIgnoreReturnValueAnnotation(Node $node): bool
    {
        $comments = $node->getComments();

        foreach ($comments as $comment) {
            $text = $comment->getText();
            $hasIgnoreReturnValueAnnotation =
                mb_strpos($text, '@ignoreReturnValue') !== false ||
                mb_strpos($text, '@void') !== false ||
                mb_strpos($text, '@return-value-ignored') !== false;

            if ($hasIgnoreReturnValueAnnotation) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if the function is in the ignored list pulled from configuration.
     */
    private function isIgnoredFunction(FuncCall $funcCall): bool
    {
        if (! $funcCall->name instanceof Node\Name) {
            return false;
        }

        $functionName = $funcCall->name->toString();

        return in_array($functionName, $this->ignoredFunctions, true);
    }

    /**
     * Get a readable name for the function/method, also accounting for static methods and functions.
     * Useful for error reporting and providing a friendly message for users.
     */
    private function getFunctionName(Node $expr, Scope $scope): string
    {
        if ($expr instanceof FuncCall && $expr->name instanceof Node\Name) {
            return 'function '.$expr->name->toString().'()';
        }

        if ($expr instanceof MethodCall && $expr->name instanceof Node\Identifier) {
            // If we have a method call, let's try to get a more precise class name from the variable type
            $classType = $scope->getType($expr->var);
            $classNames = $classType->getObjectClassNames();
            $className = count($classNames) > 0
                ? $classNames[0]
                : 'object';

            return 'method '.$className.'::'.$expr->name->toString().'()';
        }

        if ($expr instanceof StaticCall) {
            $className = $expr->class instanceof Node\Name ? $expr->class->toString() : 'unknown class';
            $methodName = $expr->name instanceof Node\Identifier ? $expr->name->toString() : 'unknown method';

            return 'static method '.$className.'::'.$methodName.'()';
        }

        return 'function call';
    }
}

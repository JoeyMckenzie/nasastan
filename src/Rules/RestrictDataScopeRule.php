<?php

declare(strict_types=1);

namespace Nasastan\Rules;

use Nasastan\NasastanConfiguration;
use Nasastan\NasastanException;
use Nasastan\NasastanRule;
use Override;
use PhpParser\Modifiers;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * Rule #6: Restrict the scope of data to the smallest possible.
 *
 * @implements NasastanRule<Node\Stmt\Class_>
 */
final readonly class RestrictDataScopeRule implements NasastanRule
{
    private int $maxClassProperties;

    /**
     * @var string[]
     */
    private array $allowedPublicProperties;

    public function __construct(NasastanConfiguration $configuration)
    {
        $this->maxClassProperties = $configuration->maxClassProperties;
        $this->allowedPublicProperties = $configuration->allowedPublicProperties;
    }

    public function getNodeType(): string
    {
        return Class_::class;
    }

    /**
     * @throws NasastanException
     */
    #[Override]
    public function processNode(Node $node, Scope $scope): array
    {
        /** @var RuleError[] $errors */
        $errors = [];

        /** @var Property[] $classProperties */
        $classProperties = array_filter($node->stmts, fn (Node $node): bool => $node instanceof Property);

        /** @var Param[] $promotedProperties */
        $promotedProperties = [];

        // We also need to check for promoted properties within the class constructor and add those to the total count
        foreach ($node->stmts as $stmt) {
            if ($stmt instanceof ClassMethod && $stmt->name->toString() === '__construct') {
                foreach ($stmt->params as $param) {
                    // Check if the parameter has property flags (promoted property, prefixed with private or public)
                    if ($param->flags !== 0) {
                        $promotedProperties[] = $param;
                    }
                }
            }
        }

        // Pretty simple, if we're over the max allowed class props, that's an error, Jack...
        $totalPropertyCount = count($promotedProperties) + count($classProperties);

        if ($totalPropertyCount > $this->maxClassProperties) {
            try {
                $errors[] = RuleErrorBuilder::message(
                    sprintf(
                        'NASA Power of Ten Rule #6: Class "%s" has %d properties, but the maximum allowed is %d.',
                        $node->name?->toString(),
                        $totalPropertyCount,
                        $this->maxClassProperties
                    )
                )->build();
            } catch (ShouldNotHappenException $e) {
                throw NasastanException::from($this->getRuleName(), $e);
            }
        }

        // Check for regular public properties that aren't in the allowed list
        foreach ($classProperties as $property) {
            if ($property->isPublic()) {
                $propertyName = $property->props[0]->name->toString();
                $this->checkPublicPropertyAllowed($propertyName, $node->name?->toString(), $errors);
            }
        }

        // Check for promoted public properties that aren't in the allowed list
        foreach ($promotedProperties as $param) {
            // Insert meme about "I'm gonna do what's called a programmer move here" with a bitwise, checking for the public flag being set
            // The param flags are combined, so we need to check for things like public and public static props
            if (($param->flags & Modifiers::PUBLIC) !== 0) {
                /** @var Variable $property */
                $property = $param->var;
                $propertyName = $property->name;

                if (is_string($propertyName)) {
                    $this->checkPublicPropertyAllowed($propertyName, $node->name?->toString(), $errors);
                }
            }
        }

        return $errors;
    }

    public function getRuleName(): string
    {
        return 'NASA Power of Ten Rule #6';
    }

    public function getRuleDescriptor(): string
    {
        return 'Restrict the scope of data to the smallest possible.';
    }

    /**
     * Check if a public property is in the allowed list
     *
     * @param  array<RuleError>  $errors
     *
     * @throws NasastanException
     */
    private function checkPublicPropertyAllowed(string $propertyName, ?string $className, array &$errors): void
    {
        $found = false;

        // If any of the public props ARE NOT allowed, that's yet another error, Jack...
        foreach ($this->allowedPublicProperties as $allowed) {
            $matchesAllowedPropertyName = preg_match('/'.str_replace('*', '.*', $allowed).'/', $propertyName);

            if ($matchesAllowedPropertyName === 1) {
                $found = true;
                break;
            }
        }

        if (! $found) {
            try {
                $errors[] = RuleErrorBuilder::message(
                    sprintf(
                        'NASA Power of Ten Rule #6: Public property "%s" in class "%s" violates data scope restriction. Consider making it private or protected.',
                        $propertyName,
                        $className
                    )
                )->build();
            } catch (ShouldNotHappenException $e) {
                throw NasastanException::from($this->getRuleName(), $e);
            }
        }
    }
}

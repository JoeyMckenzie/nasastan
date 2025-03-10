<?php

declare(strict_types=1);

namespace Nasastan\Rules;

use Nasastan\NasastanConfiguration;
use Nasastan\NasastanRule;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;

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

    public function processNode(Node $node, Scope $scope): array
    {
        /** @var RuleError[] $errors */
        $errors = [];

        /** @var Property[] $properties */
        $properties = array_filter($node->stmts, fn (Node $node): bool => $node instanceof Property);

        if (count($properties) > $this->maxClassProperties) {
            $errors[] = RuleErrorBuilder::message(
                sprintf(
                    'NASA Power of Ten Rule #6: Class "%s" has %d properties, but the maximum allowed is %d.',
                    $node->name?->toString(),
                    count($properties),
                    $this->maxClassProperties
                )
            )->build();
        }

        // Check for public properties that aren't in the allowed list
        foreach ($properties as $property) {
            if ($property->isPublic()) {
                $propertyName = $property->props[0]->name->toString();
                $found = false;

                foreach ($this->allowedPublicProperties as $allowed) {
                    $matchesAllowedPropertyName = preg_match('/'.str_replace('*', '.*', $allowed).'/', $propertyName);

                    if ($matchesAllowedPropertyName === 1) {
                        $found = true;
                        break;
                    }
                }

                if (! $found) {
                    $errors[] = RuleErrorBuilder::message(
                        sprintf(
                            'NASA Power of Ten Rule #6: Public property "%s" in class "%s" violates data scope restriction. Consider making it private or protected.',
                            $propertyName,
                            $node->name?->toString()
                        )
                    )->build();
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
}

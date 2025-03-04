<?php

namespace Nasastan\Rules;

use Nasastan\NasastanException;
use Nasastan\NasastanRule;
use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * Rule #3: No dynamic memory allocation after initialization
 * In PHP context, this means no creating new objects outside of constructors/initialization methods
 */
final class NoDynamicMemoryAllocationRule implements NasastanRule
{
    /** @var array<string> Methods considered initialization methods */
    private array $initMethods;

    public array $ruleDescriptors {
        get {
            return [
                'NASA Power of Ten Rule #3'
            ];
        }
    }

    public function __construct(array $initMethods = ['__construct', 'initialize', 'init', 'setup', 'boot', 'register'])
    {
        $this->initMethods = $initMethods;
    }

    public function getNodeType(): string
    {
        return New_::class;
    }

    /**
     * @throws NasastanException
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $currentMethod = $this->getCurrentMethodName($scope);

        // Skip if we're in an initialization method
        if ($currentMethod !== null && in_array($currentMethod, $this->initMethods, true)) {
            return [];
        }

        try {
            return [
                RuleErrorBuilder::message(
                    sprintf(
                        '%s: Object instantiation is only allowed in initialization methods.',
                        $this->ruleDescriptors[0]
                    )
                )->build(),
            ];
        } catch (ShouldNotHappenException $e) {
            throw new NasastanException($this->ruleDescriptors[0], $e);
        }
    }

    private function getCurrentMethodName(Scope $scope): ?string
    {
        $function = $scope->getFunction();
        if ($function === null) {
            return null;
        }

        return $function->getName();
    }
}
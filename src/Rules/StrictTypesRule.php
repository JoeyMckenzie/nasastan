<?php

declare (strict_types=1);

namespace Nasastan\Rules;

use Nasastan\NasastanException;
use Nasastan\NasastanRule;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * Rule #10: All code must be compiled with all warnings enabled
 * In PHP context, this means strict_types must be declared
 */
final class StrictTypesRule implements NasastanRule
{
    public array $ruleDescriptors {
        get {
            return [
                'NASA Power of Ten Rule #10'
            ];
        }
    }

    public function getNodeType(): string
    {
        return Node\Stmt\Declare_::class;
    }

    /**
     * @throws NasastanException
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof Node\Stmt\Declare_) {
            return [];
        }

        foreach ($node->declares as $declare) {
            if ($declare->key->toString() === 'strict_types' && $declare->value instanceof Node\Scalar\LNumber) {
                if ($declare->value->value === 1) {
                    return [];
                }
            }
        }

        try {
            return [
                RuleErrorBuilder::message(
                    sprintf(
                        '%s: All files must declare strict_types=1.',
                        $this->ruleDescriptors[0]
                    )
                )->build(),
            ];
        } catch (ShouldNotHappenException $e) {
            throw new NasastanException($this->ruleDescriptors[0], $e);
        }
    }
}
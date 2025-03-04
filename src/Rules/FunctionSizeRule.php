<?php

namespace Nasastan\Rules;

use Nasastan\NasastanException;
use Nasastan\NasastanRule;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * Rule #4: No function should be larger than the configured line limit
 */
final class FunctionSizeRule implements NasastanRule
{
    private int $maxLines;

    public array $ruleDescriptors {
        get {
            return [
                'NASA Power of Ten Rule #4'
            ];
        }
    }

    public function __construct(int $maxLines = 60)
    {
        $this->maxLines = $maxLines;
    }

    public function getNodeType(): string
    {
        return Node::class;
    }

    /**
     * @throws NasastanException
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if ($node instanceof Function_ || $node instanceof ClassMethod) {
            $startLine = $node->getStartLine();
            $endLine = $node->getEndLine();
            $lines = $endLine - $startLine;

            if ($lines > $this->maxLines) {
                try {
                    return [
                        RuleErrorBuilder::message(
                            sprintf(
                                '%s: Function %s has %d lines, which exceeds the maximum of %d lines.',
                                $this->ruleDescriptors[0],
                                $node->name->toString(),
                                $lines,
                                $this->maxLines
                            )
                        )->build(),
                    ];
                } catch (ShouldNotHappenException $e) {
                    throw new NasastanException($this->ruleDescriptors[0], $e);
                }
            }
        }

        return [];
    }
}
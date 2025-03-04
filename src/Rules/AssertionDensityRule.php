<?php

namespace Nasastan\Rules;

use Nasastan\NasastanException;
use Nasastan\NasastanRule;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\If_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * Rule #5 and #6: Assertion density
 * In PHP context, we'll check for assert() calls and type declarations
 */
final class AssertionDensityRule implements NasastanRule
{
    private float $minDensity;
    private int $minAssertions;

    public array $ruleDescriptors {
        get {
            return [
                'NASA Power of Ten Rule #5',
                'NASA Power of Ten Rule #6'
            ];
        }
    }

    public function __construct(float $minDensity = 0.02, int $minAssertions = 2)
    {
        $this->minDensity = $minDensity;
        $this->minAssertions = $minAssertions;
    }

    public function getNodeType(): string
    {
        return ClassMethod::class;
    }

    /**
     * @throws NasastanException
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof ClassMethod) {
            return [];
        }

        $methodName = $node->name->toString();
        $startLine = $node->getStartLine();
        $endLine = $node->getEndLine();
        $totalLines = $endLine - $startLine;

        if ($totalLines <= 0) {
            return [];
        }

        // Count assertions
        $assertions = 0;
        foreach ($node->stmts ?? [] as $stmt) {
            $assertions += $this->countAssertions($stmt);
        }

        // Count type declarations
        $assertions += count($node->params); // Parameter type hints
        if ($node->returnType !== null) {
            $assertions++;
        }

        $density = $assertions / $totalLines;

        if ($density < $this->minDensity) {
            try {
                return [
                    RuleErrorBuilder::message(
                        sprintf(
                            '%s: Method "%s" has an assertion density of %.2f%%, which is below the required %.2f%%.',
                            $this->ruleDescriptors[0],
                            $methodName,
                            $density * 100,
                            $this->minDensity * 100
                        )
                    )->build(),
                ];
            } catch (ShouldNotHappenException $e) {
                throw new NasastanException($this->ruleDescriptors[0], $e);
            }
        }

        if ($assertions < $this->minAssertions) {
            try {
                return [
                    RuleErrorBuilder::message(
                        sprintf(
                            '%s: Method "%s" has only %d assertions, but at least %d are required.',
                            $this->ruleDescriptors[1],
                            $methodName,
                            $assertions,
                            $this->minAssertions
                        )
                    )->build(),
                ];
            } catch (ShouldNotHappenException $e) {
                throw new NasastanException($this->ruleDescriptors[1], $e);
            }
        }

        return [];
    }

    /**
     * Count assertions in a node and its subnodes
     */
    private function countAssertions(Node $node): int
    {
        $count = 0;

        // Check for assert() function calls
        if ($node instanceof FuncCall && $node->name instanceof Node\Name) {
            $funcName = $node->name->toString();

            if ($funcName === 'assert' || $funcName === 'PHPUnit\Framework\Assert::assertTrue') {
                $count++;
            }
        }

        // Check for if statements (considered as implicit assertions)
        if ($node instanceof If_) {
            $count++;
        }

        // Recursively count assertions in child nodes
        foreach ($node->getSubNodeNames() as $subNodeName) {
            $subNode = $node->$subNodeName;

            if ($subNode instanceof Node) {
                $count += $this->countAssertions($subNode);
            } elseif (is_array($subNode)) {
                foreach ($subNode as $item) {
                    if ($item instanceof Node) {
                        $count += $this->countAssertions($item);
                    }
                }
            }
        }

        return $count;
    }
}
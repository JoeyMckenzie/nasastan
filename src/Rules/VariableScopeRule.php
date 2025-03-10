<?php

declare(strict_types=1);

namespace Nasastan\Rules;

use Nasastan\NasastanConfiguration;
use Nasastan\NasastanRule;
use Nasastan\Rules\Concerns\HasNodeClassType;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\NodeFinder;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Rule #6 (Part 3): Variables should be declared in the smallest possible scope.
 *
 * @implements NasastanRule<Node>
 */
final class VariableScopeRule implements NasastanRule
{
    use HasNodeClassType;

    private int $maxLinesToFirstUse;

    public function __construct(NasastanConfiguration $configuration)
    {
        $this->maxLinesToFirstUse = $configuration->maxLinesToFirstUse;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if ($node instanceof ClassMethod || $node instanceof Function_) {
            $isAbstractOrWithoutStatements = $node instanceof ClassMethod && ($node->isAbstract() || $node->stmts === null);
            $withoutStatements = ! isset($node->stmts);

            if ($isAbstractOrWithoutStatements || $withoutStatements) {
                return [];
            }

            /** @var RuleError[] $errors */
            $errors = [];
            $this->checkVariableInitializationAndUsage($node, $errors);

            return $errors;
        }

        return [];
    }

    public function getRuleName(): string
    {
        return 'NASA Power of Ten Rule #6';
    }

    public function getRuleDescriptor(): string
    {
        return 'Restrict the scope of data to the smallest possible (variables should be declared close to their use).';
    }

    /**
     * Check if variables are initialized too far from their first use.
     *
     * @param  RuleError[]  $errors
     */
    private function checkVariableInitializationAndUsage(ClassMethod|Function_ $node, array &$errors): void
    {
        $nodeFinder = new NodeFinder();

        // Find all variable assignments
        if ($node->stmts !== null) {
            $assignments = $nodeFinder->findInstanceOf($node->stmts, Assign::class);
            $variableAssignments = [];

            foreach ($assignments as $assignment) {
                if ($assignment->var instanceof Variable && is_string($assignment->var->name)) {
                    $variableName = $assignment->var->name;

                    // Skip $this as it's not a regular variable
                    if ($variableName === 'this') {
                        continue;
                    }

                    // Record the line of assignment
                    if (! isset($variableAssignments[$variableName])) {
                        $variableAssignments[$variableName] = [
                            'assignment' => $assignment,
                            'line' => $assignment->getStartLine(),
                        ];
                    }
                }
            }

            // Find all variable usages
            $usages = $nodeFinder->findInstanceOf($node->stmts, Variable::class);
            $variableFirstUsage = [];

            foreach ($usages as $usage) {
                if (is_string($usage->name)) {
                    $variableName = $usage->name;

                    // Skip $this
                    if ($variableName === 'this') {
                        continue;
                    }

                    // Record the first usage if not already recorded
                    if (! isset($variableFirstUsage[$variableName])) {
                        $variableFirstUsage[$variableName] = [
                            'usage' => $usage,
                            'line' => $usage->getStartLine(),
                        ];
                    } elseif ($usage->getStartLine() < $variableFirstUsage[$variableName]['line']) {
                        $variableFirstUsage[$variableName] = [
                            'usage' => $usage,
                            'line' => $usage->getStartLine(),
                        ];
                    }
                }
            }
            // Check distance between assignment and first usage
            foreach ($variableAssignments as $variableName => $assignmentData) {
                if (isset($variableFirstUsage[$variableName])) {
                    $usageLine = $variableFirstUsage[$variableName]['line'];
                    $assignmentLine = $assignmentData['line'];

                    // If the usage comes before assignment, it's likely a parameter or global - skip
                    if ($usageLine < $assignmentLine) {
                        continue;
                    }

                    $distance = $usageLine - $assignmentLine;

                    if ($distance > $this->maxLinesToFirstUse) {
                        $functionName = $node instanceof ClassMethod
                            ? $node->name->toString()
                            : ($node->namespacedName?->toString() ?? 'anonymous function');

                        $errors[] = RuleErrorBuilder::message(
                            sprintf(
                                'NASA Power of Ten Rule #6: Variable "$%s" in %s is initialized %d lines before its first use (line %d to %d). Maximum allowed is %d lines.',
                                $variableName,
                                $functionName,
                                $distance,
                                $assignmentLine,
                                $usageLine,
                                $this->maxLinesToFirstUse
                            )
                        )->build();
                    }
                }
            }
        }
    }
}

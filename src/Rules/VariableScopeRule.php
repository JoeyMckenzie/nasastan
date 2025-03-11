<?php

declare(strict_types=1);

namespace Nasastan\Rules;

use Nasastan\NasastanConfiguration;
use Nasastan\NasastanException;
use Nasastan\NasastanRule;
use Nasastan\Rules\Concerns\HasNodeClassType;
use Override;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\NodeFinder;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

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

    #[Override]
    public function processNode(Node $node, Scope $scope): array
    {
        // We only need to verify variable scope within methods and functions - probably could extend this if needed
        if ($node instanceof ClassMethod || $node instanceof Function_) {
            $isAbstractOrWithoutStatements = $node instanceof ClassMethod && ($node->isAbstract() || $node->stmts === null);
            $withoutStatements = ! isset($node->stmts);

            // Skip abstract methods and body-less functions
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
     * Checks if variables are initialized too far from their first use.
     *
     * @param  RuleError[]  $errors
     *
     * @throws NasastanException
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

                    // Record the line of assignment, so we can diff it later
                    if (! isset($variableAssignments[$variableName])) {
                        $variableAssignments[$variableName] = [
                            'assignment' => $assignment,
                            'line' => $assignment->getStartLine(),
                        ];
                    }
                }
            }

            // Find all variable usages within the current scope so we can check the distance from initialization to usage
            $usages = $nodeFinder->findInstanceOf($node->stmts, Variable::class);
            $variableFirstUsage = [];

            foreach ($usages as $usage) {
                // If we have a simple variable name (not a lambda), do the recording of first/last line
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

            // Roll through each assigned variable and check the distance to see if we need to report anything
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

                        try {
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
                        } catch (ShouldNotHappenException $e) {
                            throw NasastanException::from($this->getRuleName(), $e);
                        }
                    }
                }
            }
        }
    }
}

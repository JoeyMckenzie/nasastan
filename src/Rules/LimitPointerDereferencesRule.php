<?php

declare(strict_types=1);

namespace Nasastan\Rules;

use Nasastan\NasastanConfiguration;
use Nasastan\NasastanException;
use Nasastan\NasastanRule;
use Nasastan\Rules\Concerns\HasNodeClassType;
use Override;
use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * Rule #9: Limit pointer use to a single dereference, and do not use function pointers.
 *
 * @implements NasastanRule<Node>
 */
final class LimitPointerDereferencesRule implements NasastanRule
{
    use HasNodeClassType;

    private int $maxAllowedDereferences;

    public function __construct(NasastanConfiguration $configuration)
    {
        $this->maxAllowedDereferences = $configuration->maxAllowedDereferences;
    }

    /**
     * @throws NasastanException
     */
    #[Override]
    public function processNode(Node $node, Scope $scope): array
    {
        // Check for method call chains
        if ($node instanceof MethodCall) {
            $dereferences = $this->countMethodCallDereferences($node);

            if ($dereferences > $this->maxAllowedDereferences) {
                try {
                    return [
                        RuleErrorBuilder::message(
                            sprintf(
                                'NASA Power of Ten Rule #9: Method call chains should be limited to %d dereference(s). Found %d.',
                                $this->maxAllowedDereferences,
                                $dereferences
                            )
                        )->build(),
                    ];
                } catch (ShouldNotHappenException $e) {
                    throw NasastanException::from($this->getRuleName(), $e);
                }
            }
        }

        // Check for property access chains
        if ($node instanceof PropertyFetch) {
            $dereferences = $this->countPropertyFetchDereferences($node);

            if ($dereferences > $this->maxAllowedDereferences) {
                try {
                    return [
                        RuleErrorBuilder::message(
                            sprintf(
                                'NASA Power of Ten Rule #9: Property access chains should be limited to %d dereference(s). Found %d.',
                                $this->maxAllowedDereferences,
                                $dereferences
                            )
                        )->build(),
                    ];
                } catch (ShouldNotHappenException $e) {
                    throw NasastanException::from($this->getRuleName(), $e);
                }
            }
        }

        // Check for array access on property or method call result
        if ($node instanceof ArrayDimFetch && $this->maxAllowedDereferences < 2) {
            $var = $node->var;

            if ($var instanceof PropertyFetch || $var instanceof MethodCall) {
                try {
                    return [
                        RuleErrorBuilder::message(
                            sprintf(
                                'NASA Power of Ten Rule #9: Array access on property or method result exceeds allowed limit of %d dereference(s).',
                                $this->maxAllowedDereferences
                            )
                        )->build(),
                    ];
                } catch (ShouldNotHappenException $e) {
                    throw NasastanException::from($this->getRuleName(), $e);
                }
            }
        }

        // Check for variable functions (function pointers in PHP)
        if ($node instanceof FuncCall && ! ($node->name instanceof Node\Name)) {
            try {
                return [
                    RuleErrorBuilder::message(
                        'NASA Power of Ten Rule #9: Variable functions (function pointers) are not allowed.'
                    )->build(),
                ];
            } catch (ShouldNotHappenException $e) {
                throw NasastanException::from($this->getRuleName(), $e);
            }
        }

        // Check for closures (can be used as function pointers)
        if ($node instanceof Closure) {
            try {
                return [
                    RuleErrorBuilder::message(
                        'NASA Power of Ten Rule #9: Closures (function pointers) are not allowed.'
                    )->build(),
                ];
            } catch (ShouldNotHappenException $e) {
                throw NasastanException::from($this->getRuleName(), $e);
            }
        }

        // Check for callables using array syntax
        if ($node instanceof Node\Expr\Array_) {
            $items = $node->items;

            if (count($items) === 2) {
                $objectValue = $items[0]->value;
                $objectName = $items[1]->value;

                // Check if this looks like a callable array [$object, 'method']
                if ($objectName instanceof Node\Scalar\String_) {
                    $isInstanceCallable = $objectValue instanceof Variable || $objectValue instanceof PropertyFetch;

                    if ($isInstanceCallable) {
                        try {
                            return [
                                RuleErrorBuilder::message(
                                    'NASA Power of Ten Rule #9: Callable arrays (function pointers) are not allowed.'
                                )->build(),
                            ];
                        } catch (ShouldNotHappenException $e) {
                            throw NasastanException::from($this->getRuleName(), $e);
                        }
                    }
                }
            }
        }

        // Check for method calls on static calls
        if ($node instanceof StaticCall) {
            // For simplicity, we'll count static calls with a method call as 2 dereferences by default
            $dereferences = 2;

            if ($dereferences > $this->maxAllowedDereferences) {
                try {
                    return [
                        RuleErrorBuilder::message(
                            sprintf(
                                'NASA Power of Ten Rule #9: Method calls on static call results exceed allowed limit of %d dereference(s).',
                                $this->maxAllowedDereferences
                            )
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
        return 'NASA Power of Ten Rule #9';
    }

    public function getRuleDescriptor(): string
    {
        return 'Limit pointer use to a single dereference, and do not use function pointers.';
    }

    /**
     * Counts the number of dereferences in a method call chain be traversing the method chain until its end.
     */
    private function countMethodCallDereferences(MethodCall $node): int
    {
        $count = 1;
        $var = $node->var;

        // For our purpose, static calls are counted as a dereference of 2 calls
        if ($var instanceof StaticCall) {
            return 2;
        }

        while ($var instanceof MethodCall || $var instanceof PropertyFetch) {
            $count++;
            $var = $var->var;
        }

        return $count;
    }

    /**
     * Counts the number of dereferences in a property fetch chain.
     */
    private function countPropertyFetchDereferences(PropertyFetch $node): int
    {
        $count = 1;
        $var = $node->var;

        while ($var instanceof PropertyFetch || $var instanceof MethodCall) {
            $count++;
            $var = $var->var;
        }

        return $count;
    }
}

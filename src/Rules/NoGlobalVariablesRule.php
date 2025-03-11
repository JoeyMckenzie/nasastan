<?php

declare(strict_types=1);

namespace Nasastan\Rules;

use Nasastan\NasastanConfiguration;
use Nasastan\NasastanException;
use Nasastan\NasastanRule;
use Override;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Global_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * Rule #6 (Part 2): No global variables.
 *
 * @implements NasastanRule<Node\Stmt\Global_>
 */
final readonly class NoGlobalVariablesRule implements NasastanRule
{
    /**
     * @var string[]
     */
    private array $allowedGlobalVars;

    public function __construct(NasastanConfiguration $configuration)
    {
        $this->allowedGlobalVars = $configuration->allowedGlobalVars;
    }

    public function getNodeType(): string
    {
        return Global_::class;
    }

    /**
     * @throws NasastanException
     */
    #[Override]
    public function processNode(Node $node, Scope $scope): array
    {
        /** @var RuleError[] $errors */
        $errors = [];

        // Roll through each of the variables associated to the node, checking each to ensure no globals are created
        foreach ($node->vars as $var) {
            if ($var instanceof Variable && is_string($var->name)) {
                $variableName = $var->name;
                $isAllowed = false;

                // Check our configurable whitelist to determine if the variable is allowed
                foreach ($this->allowedGlobalVars as $allowedVar) {
                    $matches = preg_match('/'.str_replace('*', '.*', $allowedVar).'/', $variableName);

                    if ($matches === 1) {
                        $isAllowed = true;
                        break;
                    }
                }

                if (! $isAllowed) {
                    try {
                        $errors[] = RuleErrorBuilder::message(
                            sprintf(
                                'NASA Power of Ten Rule #6: Global variable "$%s" detected. Global variables should be avoided.',
                                $variableName
                            )
                        )->build();
                    } catch (ShouldNotHappenException $e) {
                        throw NasastanException::from($this->getRuleName(), $e);
                    }
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
        return 'Restrict the scope of data to the smallest possible (no global variables).';
    }
}

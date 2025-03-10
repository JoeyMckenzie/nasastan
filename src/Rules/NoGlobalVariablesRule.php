<?php

declare(strict_types=1);

namespace Nasastan\Rules;

use Nasastan\NasastanConfiguration;
use Nasastan\NasastanRule;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Global_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;

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

    public function processNode(Node $node, Scope $scope): array
    {
        /** @var RuleError[] $errors */
        $errors = [];

        foreach ($node->vars as $var) {
            if ($var instanceof Variable && is_string($var->name)) {
                $variableName = $var->name;
                $isAllowed = false;

                foreach ($this->allowedGlobalVars as $allowedVar) {
                    $matches = preg_match('/'.str_replace('*', '.*', $allowedVar).'/', $variableName);
                    if ($matches === 1) {
                        $isAllowed = true;
                        break;
                    }
                }

                if (! $isAllowed) {
                    $errors[] = RuleErrorBuilder::message(
                        sprintf(
                            'NASA Power of Ten Rule #6: Global variable "$%s" detected. Global variables should be avoided.',
                            $variableName
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
        return 'Restrict the scope of data to the smallest possible (no global variables).';
    }
}

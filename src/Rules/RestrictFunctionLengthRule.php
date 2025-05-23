<?php

declare(strict_types=1);

namespace NASAStan\Rules;

use NASAStan\NASAStanConfiguration;
use NASAStan\NASAStanException;
use NASAStan\NASAStanRule;
use NASAStan\Rules\Concerns\HasNodeClassType;
use NASAStan\Rules\Concerns\HasRuleEnablement;
use Override;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * Rule #4: Restrict functions to a single printed page.
 *
 * @implements NASAStanRule<Node>
 */
final readonly class RestrictFunctionLengthRule implements NASAStanRule
{
    use HasNodeClassType, HasRuleEnablement;

    public function __construct(
        private NASAStanConfiguration $configuration)
    {
        //
    }

    public function getRuleDescriptor(): string
    {
        return 'Restrict functions to a single printed page.';
    }

    /**
     * @throws NASAStanException
     */
    #[Override]
    public function processNode(Node $node, Scope $scope): array
    {
        if (! $this->enabled('rule_4')) {
            return [];
        }

        // Only need to consider class methods and functions
        if (! $node instanceof ClassMethod && ! $node instanceof Function_) {
            return [];
        }

        $functionName = $node->name->toString();

        // Skip if there's no statements, like an abstract method or interface method
        if ($node->stmts === null) {
            return [];
        }

        $startLine = $node->getStartLine();
        $endLine = $node->getEndLine();
        $totalLines = $endLine - $startLine + 1;

        // If we need to exclude comments or blank lines, we'll need to analyze the file content
        if (! $this->configuration->includeComments || ! $this->configuration->includeBlankLines) {
            $fileContent = $this->getFileContent($scope);

            if ($fileContent !== null) {
                $functionLines = array_slice(
                    explode("\n", $fileContent),
                    $startLine - 1,
                    $totalLines
                );

                $contentLines = count($functionLines);

                if (! $this->configuration->includeComments) {
                    $contentLines -= $this->countCommentLines($functionLines);
                }

                if (! $this->configuration->includeBlankLines) {
                    $contentLines -= $this->countBlankLines($functionLines);
                }

                $totalLines = $contentLines;
            }
        }

        if ($totalLines > $this->configuration->maxLines) {
            $nodeType = $node instanceof ClassMethod ? 'Method' : 'Function';

            try {
                return [
                    RuleErrorBuilder::message(
                        sprintf(
                            'NASA Power of Ten Rule #4: %s "%s" has %d lines which exceeds the maximum of %d lines (single printed page).',
                            $nodeType,
                            $functionName,
                            $totalLines,
                            $this->configuration->maxLines
                        )
                    )->build(),
                ];
            } catch (ShouldNotHappenException $e) {
                throw NASAStanException::from($this->getRuleName(), $e);
            }
        }

        return [];
    }

    public function getRuleName(): string
    {
        return 'NASA Power of Ten Rule #4';
    }

    /**
     * Gets the string content from the file we're currently analyzing, useful for checking for comments/whitespace.
     */
    private function getFileContent(Scope $scope): ?string
    {
        $file = $scope->getFile();

        // We've got bigger problems if the file we're currently analyzing doesn't exist - not even sure this can happen
        if (! file_exists($file)) {
            return null;
        }

        $contents = file_get_contents($file);

        return $contents === false
            ? null
            : $contents;
    }

    /**
     * Counts the number of comments in the current section of the file.
     *
     * @param  string[]  $lines
     */
    private function countCommentLines(array $lines): int
    {
        $commentCount = 0;
        $inMultilineComment = false;

        foreach ($lines as $line) {
            $trimmedLine = mb_trim($line);

            // Account for multi-line comment start
            if (str_contains($trimmedLine, '/*') && ! str_contains($trimmedLine, '*/')) {
                $inMultilineComment = true;
                $commentCount++;

                continue;
            }

            // Account for multi-line comment end
            if ($inMultilineComment && str_contains($trimmedLine, '*/')) {
                $inMultilineComment = false;
                $commentCount++;

                continue;
            }

            // If we're inside a multi-line comment, keep rolling the counter
            if ($inMultilineComment) {
                $commentCount++;

                continue;
            }

            // If we're inside a single-line comment, keep rolling the counter
            if (str_starts_with($trimmedLine, '//') || str_starts_with($trimmedLine, '#') ||
                (str_contains($trimmedLine, '/*') && str_contains($trimmedLine, '*/'))) {
                $commentCount++;
            }
        }

        return $commentCount;
    }

    /**
     * Counts the number of blank lines in the current file.
     *
     * @param  string[]  $lines
     */
    private function countBlankLines(array $lines): int
    {
        $blankCount = 0;

        foreach ($lines as $line) {
            if (mb_trim($line) === '') {
                $blankCount++;
            }
        }

        return $blankCount;
    }
}

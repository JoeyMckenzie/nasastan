<?php

declare(strict_types=1);

namespace Nasastan\Rules;

use Nasastan\NasastanConfiguration;
use Nasastan\NasastanRule;
use Nasastan\Rules\Concerns\HasNodeClassType;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Rule #4: Restrict functions to a single printed page.
 *
 * @implements NasastanRule<Node>
 */
final class RestrictFunctionLengthRule implements NasastanRule
{
    use HasNodeClassType;

    private int $maxLines;

    private bool $includeComments;

    private bool $includeBlankLines;

    public function __construct(NasastanConfiguration $configuration)
    {
        $this->maxLines = $configuration->maxLines;
        $this->includeBlankLines = $configuration->includeBlankLines;
        $this->includeComments = $configuration->includeComments;
    }

    public function getRuleName(): string
    {
        return 'NASA Power of Ten Rule #4';
    }

    public function getRuleDescriptor(): string
    {
        return 'Restrict functions to a single printed page.';
    }

    public function processNode(Node $node, Scope $scope): array
    {
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
        if (! $this->includeComments || ! $this->includeBlankLines) {
            $fileContent = $this->getFileContent($scope);

            if ($fileContent !== null) {
                $functionLines = array_slice(
                    explode("\n", $fileContent),
                    $startLine - 1,
                    $totalLines
                );

                $contentLines = count($functionLines);

                if (! $this->includeComments) {
                    $contentLines -= $this->countCommentLines($functionLines);
                }

                if (! $this->includeBlankLines) {
                    $contentLines -= $this->countBlankLines($functionLines);
                }

                $totalLines = $contentLines;
            }
        }

        if ($totalLines > $this->maxLines) {
            $nodeType = $node instanceof ClassMethod ? 'Method' : 'Function';

            return [
                RuleErrorBuilder::message(
                    sprintf(
                        'NASA Power of Ten Rule #4: %s "%s" has %d lines which exceeds the maximum of %d lines (single printed page).',
                        $nodeType,
                        $functionName,
                        $totalLines,
                        $this->maxLines
                    )
                )->build(),
            ];
        }

        return [];
    }

    private function getFileContent(Scope $scope): ?string
    {
        $file = $scope->getFile();

        if (! file_exists($file)) {
            return null;
        }

        $contents = file_get_contents($file);

        return $contents === false ? null : $contents;
    }

    /**
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

<?php

declare(strict_types=1);

namespace NASAStan\Rules;

use NASAStan\NASAStanConfiguration;
use NASAStan\NASAStanException;
use NASAStan\NASAStanRule;
use NASAStan\Rules\Concerns\HasNodeClassType;
use Override;
use PhpParser\Node;
use PhpParser\Node\Expr\ErrorSuppress;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\Namespace_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * Rule #10: Compile with all possible warnings active; all warnings should then be addressed before release of the software.
 *
 * @implements NASAStanRule<Node>
 */
final class CompileWithAllWarningsRule implements NASAStanRule
{
    use HasNodeClassType;

    /**
     * @var string[]
     */
    private array $disallowedErrorSuppressingFunctions;

    /**
     * @var array<array-key, int>
     */
    private array $requiredDeclareDirectives;

    /**
     * @var array<array-key, bool>
     */
    private array $fileDirectivesFound = [];

    private ?string $currentFile = null;

    public function __construct(NASAStanConfiguration $configuration)
    {
        $this->disallowedErrorSuppressingFunctions = $configuration->disallowedErrorSuppressingFunctions;
        $this->requiredDeclareDirectives = $configuration->requiredDeclareDirectives;
    }

    /**
     * @throws NASAStanException
     */
    #[Override]
    public function processNode(Node $node, Scope $scope): array
    {
        $errors = [];
        $filename = $scope->getFile();

        // Track which file we're analyzing to detect missing declarations
        if ($this->currentFile !== $filename) {
            $this->currentFile = $filename;
            $this->fileDirectivesFound = [];

            // We'll keep track of the file directives assuming each of them is missing
            foreach (array_keys($this->requiredDeclareDirectives) as $directive) {
                $this->fileDirectivesFound[$directive] = false;
            }
        }

        // Rule 1: Check for error suppression operator (@)
        if ($node instanceof ErrorSuppress) {
            try {
                $errors[] = RuleErrorBuilder::message(
                    'NASA Power of Ten Rule #10: Error suppression operator (@) is not allowed as it hides warnings.'
                )->build();
            } catch (ShouldNotHappenException $e) {
                throw NASAStanException::from($this->getRuleName(), $e);
            }
        }

        // Rule 2: Check for error suppression functions (error_reporting, etc.)
        if ($node instanceof FuncCall && $node->name instanceof Name) {
            $functionName = $node->name->toString();
            if (in_array($functionName, $this->disallowedErrorSuppressingFunctions, true)) {
                try {
                    $errors[] = RuleErrorBuilder::message(
                        sprintf('NASA Power of Ten Rule #10: Error suppressing function "%s" is not allowed.', $functionName)
                    )->build();
                } catch (ShouldNotHappenException $e) {
                    throw NASAStanException::from($this->getRuleName(), $e);
                }
            }
        }

        // Rule 3: Check for proper declare directives (strict_types, etc.)
        if ($node instanceof Declare_) {
            // Roll through each of the declares, marking each file as checked containing a valid or invalid statement
            foreach ($node->declares as $declare) {
                $directiveName = $declare->key->name;

                if (array_key_exists($directiveName, $this->requiredDeclareDirectives)) {
                    $this->fileDirectivesFound[$directiveName] = true;

                    // Check if there's a specific required value for this directive
                    $expectedValue = $this->requiredDeclareDirectives[$directiveName];

                    if ($declare->value instanceof Node\Scalar\Int_) {
                        /** @var Node\Scalar\Int_ $value */
                        $value = $declare->value;

                        if ($value->value !== $expectedValue) {
                            try {
                                $errors[] = RuleErrorBuilder::message(
                                    sprintf(
                                        'NASA Power of Ten Rule #10: Declare directive "%s" must be set to %s.',
                                        $directiveName,
                                        $expectedValue
                                    )
                                )->build();
                            } catch (ShouldNotHappenException $e) {
                                throw NASAStanException::from($this->getRuleName(), $e);
                            }
                        }
                    }
                }
            }
        }

        // Check for missing directives at the file level by examining the first namespace declaration or the end of the file
        if ($node instanceof Namespace_ || $scope->isInClass()) {
            foreach (array_keys($this->requiredDeclareDirectives) as $directive) {
                if (! $this->fileDirectivesFound[$directive]) {
                    try {
                        $errors[] = RuleErrorBuilder::message(
                            sprintf('NASA Power of Ten Rule #10: Missing required declare directive "%s".', $directive)
                        )->build();
                    } catch (ShouldNotHappenException $e) {
                        throw NASAStanException::from($this->getRuleName(), $e);
                    }

                    // Mark as found to avoid duplicate errors
                    $this->fileDirectivesFound[$directive] = true;
                }
            }
        }

        return $errors;
    }

    public function getRuleName(): string
    {
        return 'NASA Power of Ten Rule #10';
    }

    public function getRuleDescriptor(): string
    {
        return 'Compile with all possible warnings active; all warnings should then be addressed before release of the software.';
    }
}

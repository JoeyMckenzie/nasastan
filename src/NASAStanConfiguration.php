<?php

declare(strict_types=1);

namespace NASAStan;

/**
 * Global configuration for NASAStan rules based on the current PHPStan configuration file.
 */
final class NASAStanConfiguration
{
    /**
     * @param  string[]  $enabledRules
     * @param  string[]  $exceptRules
     * @param  string[]  $allowedInitMethods
     * @param  string[]  $resourceAllocationFunctions
     * @param  string[]  $assertionFunctions
     * @param  string[]  $assertionMethods
     * @param  string[]  $exceptionThrowingFunctions
     * @param  string[]  $allowedPublicProperties
     * @param  string[]  $ignoreReturnValueForFunctions
     * @param  string[]  $disallowedErrorSuppressingFunctions
     * @param  array<array-key, int>  $requiredDeclareDirectives
     */
    public function __construct(
        public array $enabledRules = ['rule_1', 'rule_2', 'rule_3', 'rule_4', 'rule_5', 'rule_6', 'rule_7', 'rule_9', 'rule_10'],
        public array $exceptRules = [],

        // Rule 2: Fixed upper bounds on loops
        public int $maxAllowedIterations = 1000,

        // Rule 3: No heap allocation after init
        public array $allowedInitMethods = ['__construct', 'initialize', 'init', 'setup', 'boot', 'register'],
        public array $resourceAllocationFunctions = ['fopen', 'curl_init', 'stream_socket_client', 'fsockopen', 'tmpfile', 'imagecreate', 'imagecreatetruecolor'],

        // Rule 4: Restrict function length
        public int $maxLines = 60,
        public bool $includeComments = true,
        public bool $includeBlankLines = true,

        // Rule 5: Minimum assertions per function
        public array $assertionFunctions = ['assert', 'assertNotNull', 'assertEquals', 'assertSame', 'assertGreaterThan', 'assertInstanceOf'],
        public array $assertionMethods = ['assertTrue', 'assertFalse', 'assertCount', 'assertNotEmpty', 'assertNull', 'assertContains'],
        public array $exceptionThrowingFunctions = ['trigger_error', 'throw', 'new Exception', 'new \Exception', 'new Error', 'new \Error'],
        public int $minimumAssertionsRequired = 2,

        // Rule 6: Restrict data scope
        public int $maxClassProperties = 10,
        public array $allowedPublicProperties = ['id', 'name', 'created_at', 'updated_at'],

        // Rule 7: Check return value
        public array $ignoreReturnValueForFunctions = ['printf', 'fprintf', 'vprintf', 'error_log', 'trigger_error', 'fwrite', 'file_put_contents', 'fputcsv', 'header'],

        // Rule 9: Limit dereferences
        public int $maxAllowedDereferences = 1,

        // Rule 10: Compile with all warnings enabled
        public array $disallowedErrorSuppressingFunctions = ['error_reporting', 'ini_set', 'set_error_handler'],
        public array $requiredDeclareDirectives = ['strict_types' => 1]
    ) {
        //
    }
}

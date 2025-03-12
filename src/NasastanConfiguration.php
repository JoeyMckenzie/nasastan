<?php

declare(strict_types=1);

namespace Nasastan;

/**
 * Global configuration for Nasastan rules based on the current PHPStan configuration file.
 */
final class NasastanConfiguration
{
    /**
     * @param  string[]  $allowedInitMethods
     * @param  string[]  $resourceAllocationFunctions
     * @param  string[]  $assertionFunctions
     * @param  string[]  $assertionMethods
     * @param  string[]  $exceptionThrowingFunctions
     * @param  string[]  $allowedGlobalVars
     * @param  string[]  $allowedPublicProperties
     * @param  string[]  $ignoreReturnValueForFunctions
     */
    public function __construct(
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
        public array $allowedGlobalVars = ['_GET', '_POST', '_SESSION', '_COOKIE', '_SERVER', '_ENV', '_FILES', '_REQUEST'],
        public array $allowedPublicProperties = ['id', 'name', 'created_at', 'updated_at'],
        public int $maxLinesToFirstUse = 10,

        // Rule 7: Check return value
        public array $ignoreReturnValueForFunctions = ['printf', 'fprintf', 'vprintf', 'error_log', 'trigger_error', 'fwrite', 'file_put_contents', 'fputcsv', 'header'],

        // Rule 9: Limit dereferences
        public int $maxAllowedDereferences = 1
    ) {
        //
    }
}

<?php

declare(strict_types=1);

namespace Nasastan;

final class NasastanConfiguration
{
    /**
     * @param  string[]  $allowedInitMethods
     * @param  string[]  $resourceAllocationFunctions
     * @param  string[]  $assertionFunctions
     * @param  string[]  $assertionMethods
     * @param  string[]  $exceptionThrowingFunctions
     */
    public function __construct(
        public int $maxAllowedIterations = 1000,
        public array $allowedInitMethods = ['__construct', 'initialize', 'init', 'setup', 'boot', 'register'],
        public array $resourceAllocationFunctions = ['fopen', 'curl_init', 'stream_socket_client', 'fsockopen', 'tmpfile', 'imagecreate', 'imagecreatetruecolor'],
        public int $maxLines = 60,
        public bool $includeComments = true,
        public bool $includeBlankLines = true,
        public array $assertionFunctions = ['assert', 'assertNotNull', 'assertEquals', 'assertSame', 'assertGreaterThan', 'assertInstanceOf'],
        public array $assertionMethods = ['assertTrue', 'assertFalse', 'assertCount', 'assertNotEmpty', 'assertNull', 'assertContains'],
        public array $exceptionThrowingFunctions = ['trigger_error', 'throw', 'new Exception', 'new \Exception', 'new Error', 'new \Error'],
        public int $minimumAssertionsRequired = 2
    ) {
        //
    }
}

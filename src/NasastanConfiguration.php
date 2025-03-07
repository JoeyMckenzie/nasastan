<?php

declare(strict_types=1);

namespace Nasastan;

final class NasastanConfiguration
{
    /**
     * @param  string[]  $allowedInitMethods
     * @param  string[]  $resourceAllocationFunctions
     */
    public function __construct(
        public int $maxAllowedIterations = 1000,
        public array $allowedInitMethods = ['__construct', 'initialize', 'init', 'setup', 'boot', 'register'],
        public array $resourceAllocationFunctions = ['fopen', 'curl_init', 'stream_socket_client', 'fsockopen', 'tmpfile', 'imagecreate', 'imagecreatetruecolor'],
        public int $maxLines = 60,
        public bool $includeComments = true,
        public bool $includeBlankLines = true
    ) {
        //
    }
}

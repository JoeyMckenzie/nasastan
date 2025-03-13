<?php

declare(strict_types=1);

// Missing strict_types declaration should be caught

namespace Tests\Examples\Rule_10;

use RuntimeException;

final class WarningSuppression
{
    public function suppressWarningsWithOperator(): void
    {
        // This should trigger an error for using the @ operator
        @file_get_contents('non_existent_file.txt');
    }

    public function suppressWarningsWithFunctions(): void
    {
        // These should trigger errors for using error suppression functions
        error_reporting(0);
        ini_set('display_errors', '0');
        set_error_handler(function () {
            return true;
        });
    }

    public function properFunction(): void
    {
        // This should be fine
        $content = file_get_contents('some_file.txt');
        if ($content === false) {
            // Handle error properly
            throw new RuntimeException('Failed to read file');
        }
    }
}

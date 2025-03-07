<?php

declare(strict_types=1);

namespace Tests\Examples\Rule_4;

final class FunctionLengthInvalid
{
    /**
     * Short method will comply with the rule.
     */
    public function shortMethod(): string
    {
        $result = '';

        for ($i = 0; $i < 5; $i++) {
            $result .= "Line $i\n";
        }

        return $result;
    }

    /**
     * This method would exceed the maximum length for a test case with a low maxLinesPerFunction setting (e.g., 20 lines).
     * It contains comments and blank lines that could be excluded from the count based on the rule configuration.
     */
    public function longMethod(): array
    {
        return [
            // Adding many lines to exceed the limit
            'Line 1',
            'Line 2',
            'Line 3',
            // More comments to increase the line count
            'Line 4',
            'Line 5',
            // Blank line below
            'Line 6',
            'Line 7',
            'Line 8',
            'Line 9',
            /*
             * Multi-line comment
             * to add more lines
             * to the function
             */
            'Line 10',
            'Line 11',
            'Line 12',
            'Line 13',
            'Line 14',
            'Line 15',
            // More and more lines
            'Line 16',
            'Line 17',
            'Line 18',
            'Line 19',
            'Line 20',
        ];
    }

    /**
     * Generate a really long method that will definitely exceed 60 lines
     */
    public function reallyLongMethod(): void
    {
        $counter = 0;

        // Let's add lots of statements to make this function very long
        $counter++;
        $counter++;
        $counter++;
        $counter++;
        $counter++;
        $counter++;
        $counter++;
        $counter++;
        $counter++;
        $counter++;

        $counter = $counter > 5 ? 5 : 10;

        $counter++;
        $counter++;
        $counter++;
        $counter++;
        $counter++;

        // More statements
        $counter++;
        $counter++;
        $counter++;
        $counter++;
        $counter++;

        // Even more statements
        $counter++;
        $counter++;
        $counter++;
        $counter++;
        $counter++;

        // Keep going...
        $counter++;
        $counter++;
        $counter++;
        $counter++;
        $counter++;

        for ($i = 0; $i < 10; $i++) {
            $counter += $i;
            // Another statement inside the loop
            $counter -= 1;
        }

        // Final set of statements
        $counter++;
        $counter++;
        $counter++;
        $counter++;
        $counter++;
        $counter++;
        $counter++;
        $counter++;
        $counter++;
        $counter++;
    }
}

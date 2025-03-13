<div align="center" style="padding-top: 2rem;">
    <img src="art/cropped.png" height="178" width="432" alt="logo"/>
    <div style="display: inline-block; margin-top: 2rem">
        <img src="https://img.shields.io/packagist/v/joeymckenzie/nasastan.svg?style=flat-square" alt="packgist downloads" />
        <img src="https://img.shields.io/github/actions/workflow/status/joeymckenzie/nasastan/run-ci.yml?branch=main&label=ci&style=flat-square" alt="ci" />
        <img src="https://img.shields.io/github/actions/workflow/status/joeymckenzie/nasastan/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square" alt="packgist downloads" />
        <img src="https://img.shields.io/packagist/dt/joeymckenzie/nasastan.svg?style=flat-square" alt="packgist downloads" />
    </div>
</div>

# NASAStan 🚀

PHPStan extension that enforces
NASA's [Power of Ten](https://en.wikipedia.org/wiki/The_Power_of_10:_Rules_for_Developing_Safety-Critical_Code)
rules in your PHP code.

> ⚠️ This is solely for self-learning and under active development. You're on your own if you it for the time being.

## Table of Contents

- [Motivation](#why-should-i-use-this-extension)
- [Getting started](#getting-started)
- [Usage](#usage)
- [Power of Ten Rules](#original-nasa-power-of-ten-rules)
- [Rules]()
    - [1. Avoid complex flow constructs](#avoid-complex-flow-constructs-such-as-goto-and-recursion)
    - [2. All loops must have fixed bounds](#all-loops-must-have-fixed-bounds-this-prevents-runaway-code)
    - [3. Avoid heap memory allocation after initialization](#avoid-heap-memory-allocation-after-initialization)
    - [4. Restrict functions to a single printed page](#restrict-functions-to-a-single-printed-page)
    - TODO
- [References](#references)
- [Contributing](#references)

## Why should I use this extension?

Great question. I'm still trying to figure out an answer to that myself.

## Getting started

To get started, install the package with composer:

```
composer require --dev joeymckenzie/nasastan
```

## Usage

If you're using `phpstan/extension-installer`, you're all set!

If not, however, include the extension in your PHPStan configuration:

```yaml
includes:
  - vendor/joeymckenzie/nasastan/extension.neon
```

## Original NASA Power of Ten Rules

1. Avoid complex flow constructs, such as goto and recursion.
2. All loops must have fixed bounds. This prevents runaway code.
3. Avoid heap memory allocation after initialization.
4. Restrict functions to a single printed page.
5. Use a minimum of two runtime assertions per function.
6. Restrict the scope of data to the smallest possible.
7. Check the return value of all non-void functions, or cast to void to indicate the return value is useless.
8. Use the preprocessor only for header files and simple macros.
9. Limit pointer use to a single dereference, and do not use function pointers.
10. Compile with all possible warnings active; all warnings should then be addressed before release of the software.

## Rules

### Rule #1

#### Avoid complex flow constructs, such as goto and recursion

Disallows the use of `goto` statements and recursive functions. The following code would be in direct violation of this
rule and reported on by NASAStan:

```php

function baz(): void
{
    start:
    $foo = 'bar';

    goto start;  // ❌ phpstan: NASA Power of Ten Rule #1: Goto statements are not allowed.
}

function factorial(int $n): int
{
    if ($n <= 1) {
        return 1;
    }

    return $n * factorial($n - 1); // ❌ phpstan: NASA Power of Ten Rule #1: Recursive method calls are not allowed.
}
```

### Rule #2

#### All loops must have fixed bounds. This prevents runaway code

Enforces all loops within PHP code to have a fixed upper bound. Things like `while(true)`, `do-while(true)`, `Generator`
types, and `array` types greater than the configurable upper-bound will cause NASAStan to flag for errors.

Unbound `while` loops

```php
<?php

declare(strict_types=1);

namespace Examples;

final class NoFixedUpperBound
{
    public function noFixedBound(): void
    {
        while (true) { // ❌ phpstan: NASA Power of Ten Rule #2: While/ do-while loop with condition "true" has no upper bound.
            echo 'I had run for three years, two months, 14 days, and 16 hours...';
        }
    }
}
```

```php
<?php

declare(strict_types=1);

namespace Examples;

final class DynamicWhileLoop
{
    public function whileWithDynamicCondition(): void
    {
        $result = $this->fetchNext();
        while ($result !== null) { // ❌ phpstan: NASA Power of Ten Rule #2: While/ do-while loop must have a verifiable fixed upper bound to prevent runaway code.
            echo $result;
            $result = $this->fetchNext();
        }
    }

    private function fetchNext(): ?string
    {
        static $count = 0;

        if ($count < 10) {
            $count++;

            return 'Data '.$count;
        }

        return null;
    }
}
```

With `Generator` types

```php
<?php

declare(strict_types=1);

namespace Examples;

use Generator;

final class ForeachWithGenerator
{
    public function foreachWithGenerator(): void
    {
        $generator = $this->createGenerator();
        foreach ($generator as $value) { // ❌ phpstan: NASA Power of Ten Rule #2: Foreach loop must iterate over a countable collection with a verifiable size bound.
            echo $value;
        }
    }

    /**
     * @return Generator<int, string>
     */
    private function createGenerator(): Generator
    {
        for ($i = 0; $i < 10; $i++) {
            yield "Item $i";
        }
    }
}
```

## Rule #3

#### Avoid heap memory allocation after initialization

Constricts resource allocation to only be allowed within approved initialization methods. Things like `new`ing up
objects outside of constructors, array allocations, etc. will cause NASAStan to report on this rule.

```php
<?php

declare(strict_types=1);

namespace Examples;

use SplDoublyLinkedList;
use stdClass;

final class DynamicHeapAllocation
{
    /**
     * @var string[]
     */
    private array $data = ['a', 'b', 'c']; // ❌ phpstan: NASA Power of Ten Rule #3: Dynamic array creation is not allowed after initialization.

    /**
     * @var SplDoublyLinkedList<string>
     */
    private readonly SplDoublyLinkedList $list;

    // This is fine because it's in a constructor (initialization)
    public function __construct()
    {
        fopen('php://memory', 'r+');
        $this->list = new SplDoublyLinkedList();
        $this->list->push('initial value');
        new stdClass(); // This is allowed in constructor
    }

    // This is fine because it's in an initialization method
    public function initialize(): void
    {
        $moreData = ['d', 'e', 'f'];
        $this->data = array_merge($this->data, $moreData);
    }

    // This will trigger a violation
    public function doSomething(): void
    {
        new stdClass();
        // Violation: Array creation after initialization
        $this->list->push('new value'); // Violation: Container method that allocates memory

        fopen('temp.txt', 'w'); // Violation: Resource allocation function
    }

    // This will also trigger violations
    public function processData(string $input): string
    {
        $result = [1];  // Violation: Non-empty array creation after initialization

        for ($i = 0; $i < mb_strlen($input); $i++) {
            $result[] = mb_strtoupper($input[$i]); // Modifying array after initialization
        }

        return implode('', $result);
    }
}
```

### Rule #4

#### Restrict functions to a single printed page

Enforces a strict method length rule within a function or method. Can be adjusted through configuration with a default
set to 60 lines.

```php
<?php

declare(strict_types=1);

namespace Examples;

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
     * This method would exceed the maximum length for a test case with a low maxLinesPerFunction setting (e.g. 20 lines).
     * It contains comments and blank lines that could be excluded from the count based on the rule configuration.
     */
    public function longMethod(): array // ❌ phpstan: NASA Power of Ten Rule #4: Method "longMethod" has 34 lines which exceeds the maximum of 20 lines (single printed page).
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
            'Line 21',
            'Line 22',
            'Line 23',
            'Line 24',
        ];
    }
}
```

## References

- [The Power of Ten – Rules for Developing Safety Critical Code](https://en.wikipedia.org/wiki/The_Power_of_10:_Rules_for_Developing_Safety-Critical_Code)
- [Original NASA JPL article by Gerard J. Holzmann](https://spinroot.com/gerard/pdf/P10.pdf)

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
    - [1. Avoid complex flow constructs](#rule-1)
    - [2. All loops must have fixed bounds](#rule-2)
    - [3. Avoid heap memory allocation after initialization](#rule-3)
    - [4. Restrict functions to a single printed page](#rule-4)
    - [5. Use a minimum of two runtime assertions per function](#rule-5)
    - [6. Restrict the scope of data to the smallest possible](#rule-6)
    - [7. Check the return value of all non-void functions](#rule-7)
    - [8. Use the preprocessor only for header files and simple macros](#rule-8)
    - [9. Limit pointer use to a single dereference](#rule-9)
    - [10. Compile with all possible warnings active](#rule-10)
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

### Rule #5

#### Use a minimum of two runtime assertions per function

Requires at least two runtime assertions either in the form of `assert()` methods, exceptions, or test-based assertions
like PHPUnit's `Assert`.

```php
<?php

declare(strict_types=1);

namespace Examples;

use InvalidArgumentException;
use PHPUnit\Framework\Assert;
use Stringable;

final class MinimumAssertionsPerFunction implements Stringable
{
    /**
     * Magic methods should be skipped.
     */
    public function __toString(): string
    {
        return 'AssertionsInFunctions';
    }

    /**
     * Only one assertion and should fail.
     */
    public function notEnoughAssertions(int $value): int
    {
        assert($value > 0, 'Value must be positive');

        return $value * 2;
    }

    /**
     * Function has no assertions and should fail.
     */
    public function noAssertions(int $value): int
    {
        return $value * 2;
    }

    /**
     * Function has 2 assertions and should pass.
     */
    public function enoughAssertions(int $value): int
    {
        assert($value > 0, 'Value must be positive');

        $result = $value * 2;

        assert($result > $value, 'Result should be greater than input');

        return $result;
    }

    /**
     * Only one assertion (through exception throw) - should fail.
     */
    public function methodWithOneAssertion(string $name): void
    {
        if ($name === '') {
            throw new InvalidArgumentException('Name cannot be empty');
        }

        echo "Hello, {$name}!";
    }

    /**
     * Two assertions (through exception throws) - should pass.
     */
    public function methodWithTwoAssertions(string $name, int $age): void
    {
        if ($name === '') {
            throw new InvalidArgumentException('Name cannot be empty');
        }

        if ($age <= 0) {
            throw new InvalidArgumentException('Age must be positive');
        }

        echo "Hello, $name! You are {$age} years old.";
    }

    /**
     * This method uses test assertion methods - should pass.
     */
    public function methodWithAssertionMethods(array $data): array
    {
        Assert::assertNotEmpty($data, 'Data cannot be empty');
        Assert::assertTrue(isset($data['id']), 'ID must be set');

        return $data;
    }
}

/**
 * Global function with enough assertions - should pass.
 */
function globalFunctionWithEnoughAssertions(array $items): int
{
    assert(is_array($items), 'Items must be an array');

    $count = count($items);

    assert($count >= 0, 'Count must be non-negative');

    return $count;
}

/**
 * Global function with not enough assertions - should fail.
 */
function globalFunctionWithNotEnoughAssertions(array $items): int
{
    return count($items);
}
```

### Rule #6

#### Restrict the scope of data to the smallest possible

This rule limits the number of properties on classes and their visibility. Properties may be whitelisted within
configuration telling NASAStan to ignore reporting on these instances.

```php
<?php

declare(strict_types=1);

namespace Examples;

/**
 * Trait should be analyzed
 */
trait SomeTrait
{
    public string $id; // Allowed

    public string $nonAllowedPublic; // Not allowed

    private string $private;
}

/**
 * Interface should be skipped
 */
interface SomeInterface
{
    public function doSomething(): void;
}

/**
 * This class has too many properties (exceeds maxClassProperties)
 */
final readonly class TooManyProperties
{
    private int $prop1;

    private int $prop2;

    private int $prop3;

    private int $prop4;

    private int $prop5;

    private int $prop6;
}

/**
 * Class with too many promoted properties from the constructor
 */
final readonly class TooManyPromotedPropertiesClass
{
    public function __construct(
        private string $prop1,
        private string $prop2,
        private string $prop3,
        private string $prop4,
        private string $prop5,
        private string $prop6,
    ) {
        // This is fine, exactly at the limit of 5 properties
    }
}

/**
 * Class with too many promoted properties from the constructor
 */
final readonly class MixOfTooManyPropertiesClass
{
    private string $prop1;

    private string $prop2;

    private string $prop3;

    public function __construct(
        private string $prop4,
        private string $prop5,
        private string $prop6,
    ) {
        // This is fine, exactly at the limit of 5 properties
    }
}

/**
 * This class has public properties, some allowed and some not allowed
 */
final class WhitelistedProperties
{
    public int $id; // This is allowed

    public string $name; // This is allowed

    public string $status; // This is not allowed

    public string $description; // This is not allowed

    public string $created_at; // This is allowed because of the wildcard pattern 'created_*'

    public string $updated_at; // This is allowed because of the wildcard pattern 'updated_*'

    public function getStatus(): string
    {
        return $this->status;
    }
}

/**
 * This class is fine - within the property limit and no disallowed public properties
 */
final readonly class ValidExample
{
    public function __construct(private string $name) {}

    public function getName(): string
    {
        return $this->name;
    }
}

/**
 * This class is within property limits and has only allowed public properties
 */
final class AllowedPublicPropertiesExample
{
    public int $id = 1;

    public string $name = 'Example';

    public string $created_date = '2023-01-01';
}
```

### Rule #7

#### Check the return value of all non-void functions, or cast to void to indicate the return value is useless

This rule enforces all method and function values to be used. Any unused values will be reported by NASAStan.

```php
<?php

declare(strict_types=1);

namespace Examples;

final class ReturnValueUsage
{
    public function correctUsage(): void
    {
        // Return value is used
        $result = $this->getNonVoidValue();
        $this->useValue($result);

        // Return value is explicitly ignored with annotation
        /** @ignoreReturnValue */
        $this->getNonVoidValue();

        // Return value from void function is not checked (correctly)
        $this->getVoidValue();

        // Ignored functions don't need to be checked
        printf('This is a test');

        // Alternative annotation style
        /** @void */
        $this->getNonVoidValue();

        /** @return-value-ignored */
        $this->getArrayValue();
    }

    public function incorrectUsage(): void
    {
        // Return value is not used (should trigger error)
        $this->getNonVoidValue();

        // This should trigger an error
        $this->getArrayValue();

        // Static method call with return value not used
        self::getStaticValue();
    }

    private static function getStaticValue(): int
    {
        return 42;
    }

    private function getNonVoidValue(): string
    {
        return 'some value';
    }

    /**
     * @param  mixed  $value
     */
    private function useValue($value): void
    {
        // Use the value
    }

    private function getVoidValue(): void
    {
        // Do something
    }

    /**
     * @return array<string, mixed>
     */
    private function getArrayValue(): array
    {
        return ['key' => 'value'];
    }
}
```

### Rule #8

#### Use the preprocessor only for header files and simple macros

This rule does not apply to PHP and has been deliberately ignored. PHP does not use preprocessors for header files nor
allows for the use of traditional macros.

### Rule #9

#### Limit pointer use to a single dereference, and do not use function pointers

This rule limits the number of times functions or properties may be derefenced (or called) within code. The amount of
dereferences is configurable.

```php
<?php

declare(strict_types=1);

namespace Examples;

use stdClass;

final class PointerDereferencing
{
    public function methodChaining(): void
    {
        $foo = new stdClass();

        // Violation: Multiple levels of method chaining
        $result = $foo->getService()->callMethod();

        // Allowed: Single level of method call
        $service = $foo->getService();
        $result = $service->callMethod();

        // Violation: Multiple levels of property access
        $value = $foo->property->nestedProperty;

        // Allowed: Single level of property access
        $property = $foo->property;
        $value = $property->nestedProperty;

        // Violation: Array access on property
        $item = $foo->items['key'];

        // Allowed: Array access on variable
        $items = $foo->items;
        $item = $items['key'];

        // Violation: Variable function (function pointer)
        $callback = 'someFunction';
        $result = $callback();

        // Violation: Closure (function pointer)
        $closure = function () {
            return 'result';
        };

        // Violation: Callable array
        $callable = [$this, 'methodName'];
        call_user_func($callable);

        // Violation: Method call on static call
        $result = SomeClass::getInstance()->doSomething();

        // Allowed: Storing static call result first
        $instance = SomeClass::getInstance();
        $result = $instance->doSomething();
    }
}

final class SomeClass
{
    public static function getInstance(): self
    {
        return new self();
    }

    public function doSomething(): string
    {
        return 'something';
    }
}
```

### Rule #10

#### Compile with all possible warnings active; all warnings should then be addressed before release of the software

This rules bans the use of `@` error suppression symbols and enforces the use of strict type declarations.

```php
<?php

// Missing strict_types declaration should be caught

namespace Examples;

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
```

## References

- [The Power of Ten – Rules for Developing Safety Critical Code](https://en.wikipedia.org/wiki/The_Power_of_10:_Rules_for_Developing_Safety-Critical_Code)
- [Original NASA JPL article by Gerard J. Holzmann](https://spinroot.com/gerard/pdf/P10.pdf)

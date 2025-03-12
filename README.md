<div align="center" style="padding-top: 2rem;">
    <img src="art/astronaut.png" height="400" width="400" alt="logo"/>
    <div style="display: inline-block; margin-top: 4rem">
        <img src="https://img.shields.io/packagist/v/joeymckenzie/nasastan.svg?style=flat-square" alt="packgist downloads" />
        <img src="https://img.shields.io/github/actions/workflow/status/joeymckenzie/nasastan/run-ci.yml?branch=main&label=ci&style=flat-square" alt="ci" />
        <img src="https://img.shields.io/github/actions/workflow/status/joeymckenzie/nasastan/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square" alt="packgist downloads" />
        <img src="https://img.shields.io/packagist/dt/joeymckenzie/nasastan.svg?style=flat-square" alt="packgist downloads" />
    </div>
</div>

# Nasastan 🚀

A PHPStan extension that enforces
NASA's [Power of Ten](https://en.wikipedia.org/wiki/The_Power_of_10:_Rules_for_Developing_Safety-Critical_Code)
rule in your PHP code.

> ⚠️ This is solely a package for self-learning. You're on your own if you use this package for the time being.

## Why should I use this extension?

Great question. I'm still trying to figure out an answer to that myself.

## Installation

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
8. Use the preprocessor only for header files and simple macros. (does not apply to PHP)
9. Limit pointer use to a single dereference, and do not use function pointers.
10. Compile with all possible warnings active; all warnings should then be addressed before release of the software.

## PHP Adaptation

Some rules are adapted to make sense in PHP:

- Rule #9 (Preprocessor use) is not applicable to PHP and is implemented as strict typing requirements
- Rule #3 (Memory allocation) checks for object instantiation outside configurable initialization methods
- Rule #5 and #6 (Assertions) count PHP type declarations and if conditions as assertions
- All threshold values are configurable to match your personal preferences

## References

- [The Power of Ten – Rules for Developing Safety Critical Code](https://en.wikipedia.org/wiki/The_Power_of_10:_Rules_for_Developing_Safety-Critical_Code)
- [Original NASA JPL article by Gerard J. Holzmann](https://spinroot.com/gerard/pdf/P10.pdf)
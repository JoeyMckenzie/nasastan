<?php

declare(strict_types=1);

namespace Tests\Examples\Rule_6;

/**
 * Empty class with just one property testing the boundary condition for maxClassProperties
 */
final class EmptyClass
{
    // This should trigger a violation because no public properties are allowed
    public string $prop;
}

/**
 * Function that creates an anonymous class with too many properties
 */
function createAnonymousClassWithTooManyProps(): object
{
    return new class
    {
        private int $prop1;

        private int $prop2;

        private int $prop3;

        private int $prop4;

        private int $prop5;

        private int $prop6;
    };
}

/**
 * Function that creates an anonymous class with a public property
 */
function createAnonymousClassWithPublicProp(): object
{
    return new class
    {
        public $publicProp = 'public';
    };
}

/**
 * Class with only promoted properties from the constructor
 */
final readonly class PromotedPropertiesClass
{
    public function __construct(
        private string $prop1,
        private string $prop2,
        private string $prop3,
        private string $prop4,
        private string $prop5,
    ) {
        // This is fine, exactly at the limit of 5 properties
    }
}

/**
 * Abstract class should be analyzed
 */
abstract class AbstractClass
{
    public string $publicProp; // This should trigger a violation

    abstract public function abstractMethod(): void;
}

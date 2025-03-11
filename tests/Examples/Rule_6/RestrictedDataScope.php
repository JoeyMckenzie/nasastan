<?php

declare(strict_types=1);

namespace Tests\Examples;

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

<?php

declare(strict_types=1);

namespace Tests\Examples\Rule_6;

/**
 * Example class for testing wildcard pattern matching in allowed public properties
 */
final class PatternMatching
{
    public int $id = 1; // Directly allowed

    public string $name = 'Test'; // Not allowed

    public string $user_name = 'test_user'; // Allowed by 'user_*' pattern

    public string $description = 'Test description'; // Not allowed

    public string $creation_date = '2025-03-10'; // Allowed by '*_date' pattern

    public string $updated_date = '2025-03-10'; // Allowed by '*_date' pattern

    public int $post_id = 100; // Allowed by '*_id' pattern

    public int $user_id = 42;
}

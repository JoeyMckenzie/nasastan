<?php

declare(strict_types=1);

final class NoFixedUpperBound
{
    public function noFixedBound(): void
    {
        while (true) {
            echo 'I had run for three years, two months, 14 days, and 16 hours...';
        }
    }
}

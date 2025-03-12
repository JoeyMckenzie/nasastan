<?php

declare(strict_types=1);

namespace Examples;

final readonly class TooManyPromotedProperties
{
    public function __construct(
        private int $prop1,
        private int $prop2,
        private int $prop3,
        private int $prop4,
        private int $prop5,
        private int $prop6,
        private int $prop7,
        private int $prop8,
        private int $prop9,
        private int $prop10,
        private int $prop11,
    ) {
        //
    }
}

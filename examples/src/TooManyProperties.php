<?php

declare(strict_types=1);

namespace Examples;

final readonly class TooManyProperties
{
    public function __construct(
        public int $prop1,
        public int $prop2,
        public int $prop3,
        public int $prop4,
        public int $prop5,
        public int $prop6,
    ) {
        //
    }
}

<?php

namespace Nasastan;

use PHPStan\Rules\Rule;

interface NasastanRule extends Rule
{
    /**
     * @var string[]
     */
    public array $ruleDescriptors { get; }
}
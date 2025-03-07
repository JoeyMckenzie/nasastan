<?php

declare(strict_types=1);

namespace Tests\Examples\Rule_3;

use SplDoublyLinkedList;
use stdClass;

final class HeapAllocationAfterInitMethods
{
    /**
     * @var string[]
     */
    private array $data = ['a', 'b', 'c'];

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
        $result = [];  // Violation: Array creation after initialization

        for ($i = 0; $i < mb_strlen($input); $i++) {
            $result[] = mb_strtoupper($input[$i]); // Modifying array after initialization
        }

        return implode('', $result);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Examples\Rule_7;

final class ReturnValueUsage
{
    public function correctUsage(): void
    {
        // Return value is used
        $this->getNonVoidValue();
        $this->useValue();

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
        $this->getStaticValue();
    }

    private function getStaticValue(): int
    {
        return 42;
    }

    private function getNonVoidValue(): string
    {
        return 'some value';
    }

    private function useValue(): void
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

<?php

namespace Tests\Unit;

use Nasastan\Rules\AssertionDensityRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * @extends RuleTestCase<AssertionDensityRule>
 */
class AssertionDensityRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new AssertionDensityRule();
    }

    /**
     * Test that classes with adequate assertion density pass the rule
     */
    #[Test]
    public function testClassWithAdequateAssertionDensity(): void
    {
        // Arrange
        $file = __DIR__ . '/../Data/AssertionDensity/GoodAssertionDensity.php';

        // Act & Assert
        $this->analyse([$file], []);
    }

    /**
     * Test that classes with low assertion density trigger errors
     */
    public function testClassWithLowAssertionDensity(): void
    {
        // Arrange
        $file = __DIR__ . '/../Data/AssertionDensity/LowAssertionDensity.php';

        // Act
        $this->analyse([$file], [
            [
                'NASA Power of Ten Rule #5: Method "lowDensityMethod" has an assertion density of 1.25%, which is below the required 2.00%.',
                10,
            ],
        ]);

        // Assert
        $errors = $this->getAnalyserErrors();
        $this->assertCount(1, $errors);
        $this->assertStringContainsString('assertion density', $errors[0]->getMessage());
        $this->assertStringContainsString('below the required', $errors[0]->getMessage());
    }

    /**
     * Test that classes with too few assertions trigger errors
     */
    public function testClassWithTooFewAssertions(): void
    {
        // Arrange
        $file = __DIR__ . '/data/assertion-density-too-few.php';

        // Act
        $this->analyse([$file]);

        // Assert
        $errors = $this->getAnalyserErrors();
        $this->assertCount(1, $errors);
        $this->assertStringContainsString('has only', $errors[0]->getMessage());
        $this->assertStringContainsString('but at least', $errors[0]->getMessage());
    }

    /**
     * Test with custom assertion density threshold
     */
    public function testWithCustomAssertionDensity(): void
    {
        // Arrange
        $file = __DIR__ . '/data/assertion-density-custom.php';
        $rule = new AssertionDensityRule(0.05, 2); // 5% threshold

        // Act
        $this->analyse([$file], [], [], $rule);

        // Assert
        $errors = $this->getAnalyserErrors();
        $this->assertCount(1, $errors);
        $this->assertStringContainsString('below the required 5.00%', $errors[0]->getMessage());
    }

    /**
     * Test with custom minimum assertions threshold
     */
    public function testWithCustomMinAssertions(): void
    {
        // Arrange
        $file = __DIR__ . '/data/assertion-density-custom-min.php';
        $rule = new AssertionDensityRule(0.02, 4); // 4 minimum assertions

        // Act
        $this->analyse([$file], [], [], $rule);

        // Assert
        $errors = $this->getAnalyserErrors();
        $this->assertCount(1, $errors);
        $this->assertStringContainsString('but at least 4 are required', $errors[0]->getMessage());
    }

    /**
     * Test that empty methods are handled correctly
     */
    public function testEmptyMethod(): void
    {
        // Arrange
        $file = __DIR__ . '/data/assertion-density-empty.php';

        // Act
        $this->analyse([$file]);

        // Assert
        $this->assertNoErrors($this->getAnalyserErrors());
    }
}
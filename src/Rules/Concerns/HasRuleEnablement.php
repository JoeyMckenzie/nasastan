<?php

declare(strict_types=1);

namespace NASAStan\Rules\Concerns;

use NASAStan\NASAStanConfiguration;

/**
 * @property-read NASAStanConfiguration $configuration
 */
trait HasRuleEnablement
{
    public function enabled(string $ruleName): bool
    {
        $enabled = in_array($ruleName, $this->configuration->enabledRules, true);
        $bypassed = in_array($ruleName, $this->configuration->exceptRules, true);

        return $enabled && ! $bypassed;
    }
}

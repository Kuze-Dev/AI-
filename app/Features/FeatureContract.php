<?php

declare(strict_types=1);

namespace App\Features;

/**
 * @phpstan-param string $name
 */
interface FeatureContract
{
    public function getLabel(): string;
}

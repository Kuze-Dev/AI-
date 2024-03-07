<?php

declare(strict_types=1);

namespace App\Features;

/**
 * @property-read string $name
 */
interface FeatureContract
{
    public function getLabel(): string;
}

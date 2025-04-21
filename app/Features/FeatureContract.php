<?php

declare(strict_types=1);

namespace App\Features;

interface FeatureContract
{
    public function getLabel(): string;
}

<?php

declare(strict_types=1);

namespace App\Features\ECommerce;

use App\Features\FeatureContract;
use Domain\Tenant\Models\Tenant;

class ColorPallete implements FeatureContract
{
    public string $name = 'ecommerce.color-pallete';

    public function resolve(Tenant $scope): mixed
    {
        return false;
    }

    #[\Override]
    public function getLabel(): string
    {
        return trans('Color Pallete');
    }
}

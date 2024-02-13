<?php

declare(strict_types=1);

namespace App\Features\Shopconfiguration\Shipping;

use App\Features\FeatureContract;
use Domain\Tenant\Models\Tenant;

class ShippingUps implements FeatureContract
{
    public string $name = 'ecommerce.ups';

    public function resolve(Tenant $scope): mixed
    {
        return false;
    }

    public function getLabel(): string
    {
        return trans('Shipping - UPS');
    }
}

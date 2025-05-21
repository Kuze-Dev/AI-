<?php

declare(strict_types=1);

namespace App\Features\Shopconfiguration\Shipping;

use App\Features\FeatureContract;
use Domain\Tenant\Models\Tenant;

class ShippingAusPost implements FeatureContract
{
    public string $name = 'ecommerce.auspost';

    public function resolve(Tenant $scope): mixed
    {
        return false;
    }

    #[\Override]
    public function getLabel(): string
    {
        return trans('Shipping - Auspost');
    }
}

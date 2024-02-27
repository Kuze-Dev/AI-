<?php

declare(strict_types=1);

namespace App\Features\ECommerce;

use App\Features\FeatureContract;
use Domain\Tenant\Models\Tenant;

class AllowGuestOrder implements FeatureContract
{
    public string $name = 'ecommerce.guest-order';

    public function resolve(Tenant $scope): mixed
    {
        return false;
    }

    public function getLabel(): string
    {
        return trans('Allow Guest Orders');
    }
}

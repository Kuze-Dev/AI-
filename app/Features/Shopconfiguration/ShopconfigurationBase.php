<?php

declare(strict_types=1);

namespace App\Features\Shopconfiguration;

use App\Features\FeatureContract;
use Domain\Tenant\Models\Tenant;

class ShopconfigurationBase implements FeatureContract
{
    public string $name = 'shopconfiguration.base';

    public function resolve(Tenant $scope): mixed
    {
        return false;
    }

    #[\Override]
    public function getLabel(): string
    {
        return trans('Shop Configuration');
    }
}

<?php

declare(strict_types=1);

namespace App\Features\Shopconfiguration;

use App\Features\FeatureContract;
use Domain\Tenant\Models\Tenant;

class TaxZone implements FeatureContract
{
    public string $name = 'shopconfiguration.taxzone';

    public function resolve(Tenant $scope): mixed
    {
        return false;
    }

    #[\Override]
    public function getLabel(): string
    {
        return trans('TaxZone');
    }
}

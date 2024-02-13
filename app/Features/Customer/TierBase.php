<?php

declare(strict_types=1);

namespace App\Features\Customer;

use App\Features\FeatureContract;
use Domain\Tenant\Models\Tenant;

class TierBase implements FeatureContract
{
    public string $name = 'customer.tier-base';

    public function resolve(Tenant $scope): mixed
    {
        return false;
    }

    public function getLabel(): string
    {
        return trans('Tier');
    }
}

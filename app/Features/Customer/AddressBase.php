<?php

declare(strict_types=1);

namespace App\Features\Customer;

use App\Features\FeatureContract;
use Domain\Tenant\Models\Tenant;

class AddressBase implements FeatureContract
{
    public string $name = 'customer.address-base';

    public function resolve(Tenant $scope): mixed
    {
        return false;
    }

    public function getLabel(): string
    {
        return trans('Address');
    }
}

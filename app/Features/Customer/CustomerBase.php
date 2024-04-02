<?php

declare(strict_types=1);

namespace App\Features\Customer;

use App\Features\FeatureContract;
use Domain\Tenant\Models\Tenant;

class CustomerBase implements FeatureContract
{
    public string $name = 'customer.base';

    public function resolve(Tenant $scope): mixed
    {
        return false;
    }

    #[\Override]
    public function getLabel(): string
    {
        return trans('Customer');
    }
}

<?php

declare(strict_types=1);

namespace App\Features\Customer;

use Domain\Tenant\Models\Tenant;

class TierBase
{
    public string $name = 'tier.base';

    public string $label = 'Tier';

    public function resolve(Tenant $scope): mixed
    {
        return false;
    }
}

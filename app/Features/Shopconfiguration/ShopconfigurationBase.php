<?php

declare(strict_types=1);

namespace App\Features\Shopconfiguration;

use Domain\Tenant\Models\Tenant;

class ShopconfigurationBase
{
    public string $name = 'shopconfiguration.base';

    public string $label = 'Shop Configuration';

    public function resolve(Tenant $scope): mixed
    {
        return false;
    }
}

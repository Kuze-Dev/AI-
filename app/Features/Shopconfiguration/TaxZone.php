<?php

declare(strict_types=1);

namespace App\Features\Shopconfiguration;

use Domain\Tenant\Models\Tenant;

class TaxZone
{
    public string $name = 'shopconfiguration.taxzone';

    public string $label = 'TaxZone';

    public function resolve(Tenant $scope): mixed
    {
        return false;
    }
}

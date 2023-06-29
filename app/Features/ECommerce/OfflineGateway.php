<?php

declare(strict_types=1);

namespace App\Features\ECommerce;

use Domain\Tenant\Models\Tenant;

class OfflineGateway
{
    public string $name = 'ecommerce.offline';

    public string $label = 'Offline Payment';

    public function resolve(Tenant $scope): mixed
    {
        return false;
    }
}

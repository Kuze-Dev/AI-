<?php

declare(strict_types=1);

namespace App\Features\ECommerce;

use Domain\Tenant\Models\Tenant;

class RewardPoints
{
    public string $name = 'ecommerce.reward-points';

    public string $label = 'Reward Points';

    public function resolve(Tenant $scope): mixed
    {
        return false;
    }
}

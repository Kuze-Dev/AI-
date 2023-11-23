<?php

declare(strict_types=1);

namespace App\Features\ECommerce;

use Domain\Tenant\Models\Tenant;

class ColorPallete
{
    public string $name = 'ecommerce.color-pallete';

    public string $label = 'Color Pallete';

    public function resolve(Tenant $scope): mixed
    {
        return false;
    }
}

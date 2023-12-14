<?php

declare(strict_types=1);

namespace App\Features\ECommerce;

use Domain\Tenant\Models\Tenant;

class ProductBatchUpdate
{
    public string $name = 'ecommerce.product-batch-update';

    public string $label = 'Product Batch Update';

    public function resolve(Tenant $scope): mixed
    {
        return false;
    }
}

<?php

declare(strict_types=1);

namespace Domain\Product\Actions;

use Domain\Product\Models\Product;

class DeleteProductAction
{
    public function execute(Product $product, bool $force = false): bool
    {
        return $product->{$force ? 'forceDelete' : 'delete'}();
    }
}

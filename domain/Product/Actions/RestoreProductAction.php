<?php

declare(strict_types=1);

namespace Domain\Product\Actions;

use Domain\Product\Models\Product;

class RestoreProductAction
{
    public function execute(Product $product): ?bool
    {
        if ( ! $product->trashed()) {
            return null;
        }

        return $product->restore();
    }
}

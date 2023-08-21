<?php

declare(strict_types=1);

namespace Domain\Product\Actions;

use Domain\Product\DataTransferObjects\ProductVariantData;
use Domain\Product\Models\ProductVariant;

class CreateProductVariantAction
{
    public function execute(ProductVariantData $productVariant): ProductVariant
    {
        return  ProductVariant::create($productVariant);
    }
}

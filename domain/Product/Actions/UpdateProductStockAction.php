<?php

declare(strict_types=1);

namespace Domain\Product\Actions;

use Domain\Product\Models\Product;
use Domain\Product\Models\ProductVariant;

class UpdateProductStockAction
{
    public function execute(string $purchasableType, int $purchasableId, int $quantity, ?bool $toAdd = true): void
    {
        $model = null;

        if ($purchasableType == Product::class) {
            $model = Product::find($purchasableId);
        } elseif ($purchasableType == ProductVariant::class) {
            $model = ProductVariant::find($purchasableId);
        }

        if ($model) {
            $model->stock = $toAdd ? $model->stock + $quantity : $model->stock - $quantity;
            $model->save();
        }
    }
}

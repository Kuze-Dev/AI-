<?php

declare(strict_types=1);

namespace Domain\Cart\Helpers\PrivateCart;

use Domain\Product\Enums\DiscountAmountType;
use Domain\Product\Models\Product;

class ComputedTierSellingPrice
{
    public function execute(Product $product, float $selling_price): float|int
    {
        /** @var \Domain\Tier\Models\Tier $tier */
        $tier = $product->productTier->first();

        /** @phpstan-ignore property.notFound */
        $productDiscount = $tier->pivot;

        $amount = $productDiscount->discount;
        $type = $productDiscount->discount_amount_type;

        if ($type == DiscountAmountType::FIXED_VALUE->value) {
            $selling_price -= $amount;
        } else {
            $selling_price = $selling_price - ($selling_price * $amount / 100);
        }

        return $selling_price >= 0 ? $selling_price : 0;
    }
}

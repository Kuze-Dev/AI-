<?php

declare(strict_types=1);

namespace Domain\Cart\Actions;

use Domain\Cart\Models\CartLine;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductVariant;

class SanitizeCartAction
{
    public function sanitizeGuest(mixed $model): mixed
    {
        $model->cartLines = $model->cartLines->filter(function (CartLine $cartLine) {

            if ($cartLine->purchasable instanceof Product) {
                /** @var \Domain\Product\Models\Product $product */
                $product = $cartLine->purchasable;

                return $product->allow_guest_purchase;
            } elseif ($cartLine->purchasable instanceof ProductVariant) {
                /** @var \Domain\Product\Models\Product $product */
                $product = $cartLine->purchasable->product;

                return $product->allow_guest_purchase;
            }

            return $cartLine->purchasable !== null;
        });

        return $model;
    }
}

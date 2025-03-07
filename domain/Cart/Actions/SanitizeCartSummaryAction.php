<?php

declare(strict_types=1);

namespace Domain\Cart\Actions;

use Domain\Cart\Events\SanitizeCartEvent;
use Domain\Cart\Models\CartLine;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductVariant;

class SanitizeCartSummaryAction
{
    public function sanitizeGuest(mixed $cartLines): void
    {
        $cartLineIdsTobeRemoved = [];

        $cartLines = $cartLines->filter(function (CartLine $cartLine) use (&$cartLineIdsTobeRemoved) {
            if ($cartLine->purchasable instanceof Product) {
                /** @var \Domain\Product\Models\Product $product */
                $product = $cartLine->purchasable;

                if (! $product->allow_guest_purchase) {
                    $cartLineIdsTobeRemoved[] = $cartLine->uuid;
                }
            } elseif ($cartLine->purchasable instanceof ProductVariant) {
                /** @var \Domain\Product\Models\Product $product */
                $product = $cartLine->purchasable->product;

                if (! $product->allow_guest_purchase) {
                    $cartLineIdsTobeRemoved[] = $cartLine->uuid;
                }
            }

            if ($cartLine->purchasable === null) {
                $cartLineIdsTobeRemoved[] = $cartLine->uuid;
            }

            return $cartLine->purchasable !== null;
        });

        if (! empty($cartLineIdsTobeRemoved)) {
            event(new SanitizeCartEvent(
                $cartLineIdsTobeRemoved,
            ));
        }
    }
}

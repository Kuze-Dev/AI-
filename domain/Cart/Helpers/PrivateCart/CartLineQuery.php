<?php

declare(strict_types=1);

namespace Domain\Cart\Helpers\PrivateCart;

use Domain\Cart\Models\CartLine;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductVariant;
use Domain\Tier\Models\Tier;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CartLineQuery
{
    /** @return \Illuminate\Database\Eloquent\Collection<int, \Domain\Cart\Models\CartLine> */
    public function execute(array $cartLineIds): Collection
    {
        /** @var \Domain\Customer\Models\Customer $customer */
        $customer = auth()->user();

        /** @var \Domain\Tier\Models\Tier $tier */
        $tier = $customer->tier ?? Tier::query()->where('name', config('domain.tier.default'))->first();

        $cartLines = CartLine::query()
            ->with(['purchasable' => function ($query) use ($tier) {
                $query->morphWith([
                    Product::class => [
                        'productTier' => function (BelongsToMany $query) use ($tier) {
                            $query->where('tier_id', $tier->id);
                        },
                    ],
                    ProductVariant::class => [
                        'product.productTier' => function (BelongsToMany $query) use ($tier) {
                            $query->where('tier_id', $tier->id);
                        },
                    ],
                ]);
            }])
            ->whereHas('cart', function ($query) {
                $query->whereBelongsTo(auth()->user());
            })
            ->whereNull('checked_out_at')
            ->whereIn((new CartLine())->getRouteKeyName(), $cartLineIds)
            ->get();

        return $cartLines;
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, \Domain\Cart\Models\CartLine> */
    public function guests(array $cartLineIds, string $sessionId): Collection
    {
        $cartLines = CartLine::query()
            ->with('purchasable')
            ->whereHas('cart', function ($query) use ($sessionId) {
                $query->where('session_id', $sessionId);
            })
            ->whereNull('checked_out_at')
            ->whereIn((new CartLine())->getRouteKeyName(), $cartLineIds)
            ->get();

        return $cartLines;
    }
}

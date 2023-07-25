<?php

declare(strict_types=1);

namespace Domain\Cart\Actions;

use Domain\Cart\Models\CartLine;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

class PurchasableCheckerAction
{
    public function checkStock(array $cartLineIds): int
    {
        return CartLine::with('purchasable')
            ->whereHas('cart', function ($query) {
                $query->whereBelongsTo(auth()->user());
            })
            ->whereNull('checked_out_at')
            ->whereIn((new CartLine())->getRouteKeyName(), $cartLineIds)
            ->where(function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('purchasable_type', Product::class)
                        ->whereExists(function ($subSubQuery) {
                            $subSubQuery->select('id')
                                ->from('products')
                                ->whereColumn('products.id', 'cart_lines.purchasable_id')
                                ->where('stock', '>', DB::raw('cart_lines.quantity'));
                        });
                })->orWhere(function ($subQuery) {
                    $subQuery->where('purchasable_type', ProductVariant::class)
                        ->whereExists(function ($subSubQuery) {
                            $subSubQuery->select('id')
                                ->from('product_variants')
                                ->whereColumn('product_variants.id', 'cart_lines.purchasable_id')
                                ->where('stock', '>', DB::raw('cart_lines.quantity'));
                        });
                });
            })
            ->count();
    }

    public function checkAuth(array $cartLineIds): int
    {
        return CartLine::with('purchasable')
            ->whereIn((new CartLine())->getRouteKeyName(), $cartLineIds)
            ->whereHas('cart', function ($query) {
                $query->whereBelongsTo(auth()->user());
            })
            ->whereNull('checked_out_at')
            ->count();
    }
}

<?php

declare(strict_types=1);

namespace Domain\Cart\Actions;

use Domain\Cart\DataTransferObjects\CheckoutData;
use Domain\Cart\Enums\CartActionResult;
use Domain\Cart\Models\CartLine;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CheckoutAction
{
    public function execute(CheckoutData $checkoutData): string|CartActionResult
    {
        $cartLinesForCheckout = CartLine::with('purchasable')
            ->whereHas('cart', function ($query) {
                $query->whereBelongsTo(auth()->user());
            })
            ->whereNull('checked_out_at')
            ->whereIn('id', $checkoutData->cart_line_ids)
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
            ->get();

        Log::info($cartLinesForCheckout->count());
        Log::info(count($checkoutData->cart_line_ids));
        if ($cartLinesForCheckout->count() !== count($checkoutData->cart_line_ids)) {
            throw new BadRequestHttpException('Invalid request');
        }

        $cartLineIds = $cartLinesForCheckout->pluck('id');

        $checkoutReference = Str::upper(Str::random(12));

        $affectedRows = CartLine::whereIn('id', $cartLineIds)
            ->update([
                'checkout_reference' => $checkoutReference,
                'checkout_expiration' => now()->addMinutes(20),
            ]);

        if ($affectedRows == $cartLineIds->count()) {
            return $checkoutReference;
        }

        return CartActionResult::FAILED;
    }
}

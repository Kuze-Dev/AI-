<?php

declare(strict_types=1);

namespace Domain\Cart\Actions;

use Domain\Cart\DataTransferObjects\CartStoreData;
use Domain\Cart\Models\Cart;
use Domain\Cart\Models\CartLine;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

class CreateCartLineAction
{
    public function execute(Cart $cart, CartStoreData $cartLineData): CartLine
    {
        DB::beginTransaction();

        try {
            $purchasableType = '';

            match ($cartLineData->purchasable_type) {
                'Product' => $purchasableType = Product::class,
                // 'Service' => $purchasableType = Service::class,
                // 'Booking' => $purchasableType = Booking::class,
            };

            $variant = ProductVariant::whereProductId($cartLineData->purchasable_id)
                ->whereJsonContains('combination', $cartLineData->variant)
                ->first();

            $cartLine = CartLine::updateOrCreate(
                [
                    'cart_id' => $cart->id,
                    'purchasable_id' => $cartLineData->purchasable_id,
                    'variant_id' => $variant ? $variant->id : null,
                    'purchasable_type' => $purchasableType,
                    'checked_out_at' => null,
                ],
                [
                    'quantity' => DB::raw('quantity + ' . $cartLineData->quantity),
                    'notes' => $cartLineData->notes,
                ]
            );

            DB::commit();
            return $cartLine;
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
        }
    }
}

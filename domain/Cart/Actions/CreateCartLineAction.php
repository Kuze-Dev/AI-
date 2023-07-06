<?php

declare(strict_types=1);

namespace Domain\Cart\Actions;

use Domain\Cart\DataTransferObjects\CartStoreData;
use Domain\Cart\Models\Cart;
use Domain\Cart\Models\CartLine;
use Domain\Product\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

class CreateCartLineAction
{
    public function execute(Cart $cart, CartStoreData $cartStoreData): CartLine
    {
        DB::beginTransaction();

        try {
            $purchasableType = '';

            match ($cartStoreData->purchasable_type) {
                'Product' => $purchasableType = Product::class,
                // 'Service' => $purchasableType = Service::class,
                // 'Booking' => $purchasableType = Booking::class,
            };

            $variant = ProductVariant::where('product_id', $cartStoreData->purchasable_id)
                ->whereJsonContains('combination', $cartStoreData->variant)
                ->first();

            $cartLine = CartLine::updateOrCreate(
                [
                    'cart_id' => $cart->id,
                    'purchasable_id' => $cartStoreData->purchasable_id,
                    'variant_id' => $variant ? $variant->id : null,
                    'purchasable_type' => $purchasableType,
                    'checked_out_at' => null,
                ],
                [
                    'quantity' => DB::raw('quantity + ' . $cartStoreData->quantity),
                    'notes' => $cartStoreData->notes,
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

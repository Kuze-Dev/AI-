<?php

declare(strict_types=1);

namespace Domain\Cart\Actions;

use Domain\Cart\DataTransferObjects\CartStoreData;
use Domain\Cart\Models\Cart;
use Domain\Cart\Models\CartLine;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class CreateCartLineAction
{
    public function execute(Cart $cart, CartStoreData $cartLineData): CartLine
    {
        DB::beginTransaction();

        try {
            $purchasableId = $cartLineData->purchasable_id;
            $purchasableType = '';

            match ($cartLineData->purchasable_type) {
                'Product' => $purchasableType = Product::class,
                // 'Service' => $purchasableType = Service::class,
                // 'Booking' => $purchasableType = Booking::class,
            };

            $productVariant = ProductVariant::whereProductId($cartLineData->purchasable_id)
                ->whereJsonContains('combination', $cartLineData->variant)
                ->first();

            if ($productVariant) {
                $purchasableId = $productVariant->id;
                $purchasableType = ProductVariant::class;
            }

            $cartLine = CartLine::updateOrCreate(
                [
                    'cart_id' => $cart->id,
                    'purchasable_id' => $purchasableId,
                    'purchasable_type' => $purchasableType,
                    'checked_out_at' => null,
                ],
                [
                    'quantity' => DB::raw('quantity + ' . $cartLineData->quantity),
                    'meta' => $cartLineData->meta,
                ]
            );

            DB::commit();

            return $cartLine;
        } catch (Exception $e) {
            DB::rollBack();
            Log::info($e);

            return $e;
        }
    }
}

<?php

declare(strict_types=1);

namespace Domain\Cart\Actions;

use Domain\Cart\DataTransferObjects\CreateCartData;
use Domain\Cart\Models\Cart;
use Domain\Cart\Models\CartLine;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Exception;

class CreateCartLineAction
{
    public function execute(Cart $cart, CreateCartData $cartLineData): CartLine|Exception
    {
        DB::beginTransaction();

        try {
            $purchasableId = $cartLineData->purchasable_id;
            $purchasableType = '';

            match ($cartLineData->purchasable_type) {
                'Product' => $purchasableType = Product::class,
                // 'Service' => $purchasableType = Service::class,
                // 'Booking' => $purchasableType = Booking::class,
                default => null
            };

            $productVariant = ProductVariant::find($cartLineData->variant_id);

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
                    'remarks' => $cartLineData->remarks,
                ]
            );

            $cartLine->clearMediaCollection('cart_line_notes');

            if ($cartLineData->medias !== null) {
                foreach ($cartLineData->medias as $imageUrl) {
                    try {
                        $cartLine->addMediaFromUrl($imageUrl)
                            ->toMediaCollection('cart_line_notes');
                    } catch (Exception $e) {
                        // Log::info($e);
                    }
                }
            }

            DB::commit();

            return $cartLine;
        } catch (Exception $e) {
            DB::rollBack();
            // Log::info($e);

            return $e;
        }
    }
}

<?php

declare(strict_types=1);

namespace Domain\Cart\Actions;

use Domain\Cart\DataTransferObjects\CreateCartData;
use Domain\Cart\Models\Cart;
use Domain\Cart\Models\CartLine;
use Domain\Media\Actions\CreateMediaFromUrlAction;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CreateCartLineAction
{
    public function __construct(
        private readonly CreateMediaFromUrlAction $createMediaFromUrlAction
    ) {
    }


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

            $cartLine = CartLine::where([
                'cart_id' => $cart->id,
                'purchasable_id' => $purchasableId,
                'purchasable_type' => $purchasableType,
                'checked_out_at' => null,
            ])->first();

            if ($cartLine) {
                $cartLine->update([
                    'quantity' => $cartLine->quantity + $cartLineData->quantity,
                    'remarks' => $cartLineData->remarks,
                ]);
            } else {
                $cartLine = CartLine::create([
                    'uuid' => (string) Str::uuid(),
                    'cart_id' => $cart->id,
                    'purchasable_id' => $purchasableId,
                    'purchasable_type' => $purchasableType,
                    'quantity' => $cartLineData->quantity,
                    'remarks' => $cartLineData->remarks,
                ]);
            }

            if ($cartLineData->medias !== null) {
                $this->createMediaFromUrlAction->execute($cartLine, $cartLineData->medias, 'cart_line_notes');
            }

            DB::commit();

            return $cartLine;
        } catch (Exception $e) {
            DB::rollBack();
            Log::info('Error on CreateCartLineAction->execute() ' . $e);
            return $e;
        }
    }
}

<?php

declare(strict_types=1);

namespace Domain\Cart\Actions;

use Domain\Cart\DataTransferObjects\CartQuantityUpdateData;
use Domain\Cart\Models\CartLine;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductVariant;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CartQuantityUpdateAction
{
    public function execute(CartQuantityUpdateData $cartLineData)
    {
        $customerId = auth()->user()->id;

        $cartLine = CartLine::where('id', $cartLineData->cart_line_id)
            ->whereHas('cart', function ($query) use ($customerId) {
                $query->whereCustomerId($customerId);
            })
            ->whereNull('checked_out_at')->first();

        if (!$cartLine) {
            throw new ModelNotFoundException;
        }

        $product = null;

        if (is_null($cartLine->variant_id)) {
            $product = Product::find($cartLine->purchasable_id);
        } else {
            $product = ProductVariant::find($cartLine->variant_id);
        }

        if (
            $cartLineData->action === "decrease" && $cartLine->quantity <= 1 ||
            $cartLineData->action === "increase" && $cartLine->quantity >= $product->stock
        ) {
            return [
                'message' => "Invalid Action"
            ];
        } else if ($cartLineData->action === "edit" && $cartLineData->quantity > $product->stock) {
            return [
                'message' => "Quantity exceeds product stock"
            ];
        }

        match ($cartLineData->action) {
            "increase" => $cartLine->increment("quantity"),
            "decrease" => $cartLine->decrement("quantity"),
            "edit" =>  $cartLine->update(
                [
                    'quantity' => $cartLineData->quantity
                ]
            )
        };

        return $cartLine;
    }
}

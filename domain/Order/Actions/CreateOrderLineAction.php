<?php

declare(strict_types=1);

namespace Domain\Order\Actions;

use Domain\Order\DataTransferObjects\PreparedOrderData;
use Domain\Order\Models\Order;
use Domain\Order\Models\OrderLine;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductVariant;

class CreateOrderLineAction
{
    public function execute(Order $order, PreparedOrderData $preparedOrderData)
    {
        foreach ($preparedOrderData->cartLine as $cartLine) {
            $subTotal = $cartLine->purchasable->selling_price * $cartLine->quantity;

            $name = null;
            if ($cartLine->purchasable instanceof Product) {
                $name = $cartLine->purchasable->name;
            } else if ($cartLine->purchasable instanceof ProductVariant) {
                $name = $cartLine->purchasable->product->name;
            }

            //add tax minus discount
            $total = 0 + $subTotal - 0;

            $orderLine = OrderLine::create([
                'order_id' => $order->id,
                'purchasable_id' => $cartLine->purchasable_id,
                'purchasable_type' => $cartLine->purchasable_type,
                'purchasable_sku' => $cartLine->purchasable->sku,
                'name' => $name,
                'unit_price' => $cartLine->purchasable->selling_price,
                'quantity' => $cartLine->quantity,
                'tax_total' => 0,
                'sub_total' => $subTotal,
                'discount_total' => 0,
                'total' => $total,
                'remarks_data' => $cartLine->remarks,
                'purchasable_data' => $cartLine->purchasable
            ]);

            if ($cartLine->purchasable instanceof Product) {
                $purchasableMedias = $cartLine->purchasable->getMedia('image');
                $this->copyMediaToOrderLine($orderLine, $purchasableMedias, 'order_line_images');
            } else if ($cartLine->purchasable instanceof ProductVariant) {
                $purchasableMedias = $cartLine->purchasable->product->getMedia('image');
                $this->copyMediaToOrderLine($orderLine, $purchasableMedias, 'order_line_images');
            }

            $cartLineRemarks = $cartLine->getMedia('cart_line_notes');
            $this->copyMediaToOrderLine($orderLine, $cartLineRemarks, 'order_line_notes');
        }
    }

    private function copyMediaToOrderLine($orderLine, $medias, string $collection)
    {
        foreach ($medias as $media) {
            $orderLine->addMediaFromUrl($media->getUrl())->toMediaCollection($collection);
        }
    }
}

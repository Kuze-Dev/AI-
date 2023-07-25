<?php

declare(strict_types=1);

namespace Domain\Order\Actions;

use Domain\Cart\Actions\CartSummaryAction;
use Domain\Cart\DataTransferObjects\CartSummaryShippingData;
use Domain\Cart\DataTransferObjects\CartSummaryTaxData;
use Domain\Order\DataTransferObjects\PlaceOrderData;
use Domain\Order\DataTransferObjects\PreparedOrderData;
use Domain\Order\Models\Order;
use Domain\Order\Models\OrderLine;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductVariant;
use Illuminate\Support\Str;

class CreateOrderLineAction
{
    public function execute(Order $order,  PlaceOrderData $placeOrderData, PreparedOrderData $preparedOrderData)
    {
        foreach ($preparedOrderData->cartLine as $cartLine) {

            $summary = app(CartSummaryAction::class)->getSummary(
                $cartLine,
                new CartSummaryTaxData(
                    $placeOrderData->taxation_data->country_id,
                    $placeOrderData->taxation_data->state_id
                ),
                new CartSummaryShippingData(
                    $preparedOrderData->customer,
                    $preparedOrderData->shippingAddress,
                    $preparedOrderData->shippingMethod
                ),
                $preparedOrderData->discount,
            );

            $name = null;
            if ($cartLine->purchasable instanceof Product) {
                $name = $cartLine->purchasable->name;
            } elseif ($cartLine->purchasable instanceof ProductVariant) {
                $name = $cartLine->purchasable->product->name;
            }

            $orderLine = OrderLine::create([
                'uuid' => (string) Str::uuid(),
                'order_id' => $order->id,
                'purchasable_id' => $cartLine->purchasable_id,
                'purchasable_type' => $cartLine->purchasable_type,
                'purchasable_sku' => $cartLine->purchasable->sku,
                'name' => $name,
                'unit_price' => $cartLine->purchasable->selling_price,
                'quantity' => $cartLine->quantity,
                'tax_total' => $summary->taxTotal,
                'tax_display' => $summary->taxDisplay,
                'tax_percentage' => $summary->taxPercentage,
                'sub_total' => $summary->subTotal,
                'discount_total' => 0,
                'total' => $summary->grandTotal,
                'remarks_data' => $cartLine->remarks,
                'purchasable_data' => $cartLine->purchasable,
            ]);

            if ($cartLine->purchasable instanceof Product) {
                $purchasableMedias = $cartLine->purchasable->getMedia('image');
                $this->copyMediaToOrderLine($orderLine, $purchasableMedias, 'order_line_images');
            } elseif ($cartLine->purchasable instanceof ProductVariant) {
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

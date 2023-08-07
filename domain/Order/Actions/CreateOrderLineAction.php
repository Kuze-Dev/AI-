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
use Domain\Shipment\API\USPS\Exceptions\USPSServiceNotFoundException;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;

class CreateOrderLineAction
{
    public function execute(Order $order,  PlaceOrderData $placeOrderData, PreparedOrderData $preparedOrderData): void
    {
        foreach ($preparedOrderData->cartLine as $cartLine) {

            try {
                /** @var \Domain\Address\Models\State $state */
                $state = $preparedOrderData->billingAddress->state;

                /** @var \Domain\Address\Models\Country $country */
                $country = $state->country;

                $summary = app(CartSummaryAction::class)->getSummary(
                    $cartLine,
                    new CartSummaryTaxData(
                        $country->id,
                        $state->id,
                    ),
                    new CartSummaryShippingData(
                        $preparedOrderData->customer,
                        $preparedOrderData->shippingAddress,
                        $preparedOrderData->shippingMethod
                    ),
                    $preparedOrderData->discount,
                    $placeOrderData->serviceId
                );
            } catch (USPSServiceNotFoundException) {
                throw new USPSServiceNotFoundException();
            }

            $name = null;
            if ($cartLine->purchasable instanceof Product) {
                /** @var \Domain\Product\Models\Product $product */
                $product = $cartLine->purchasable;

                $name = $product->name;
            } elseif ($cartLine->purchasable instanceof ProductVariant) {
                /** @var \Domain\Product\Models\Product $product */
                $product = $cartLine->purchasable->product;

                $name = $product->name;
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
                /** @var \Domain\Product\Models\Product $product */
                $product = $cartLine->purchasable->product;

                $purchasableMedias = $product->getMedia('image');
                $this->copyMediaToOrderLine($orderLine, $purchasableMedias, 'order_line_images');
            }

            $cartLineRemarks = $cartLine->getMedia('cart_line_notes');
            $this->copyMediaToOrderLine($orderLine, $cartLineRemarks, 'order_line_notes');
        }
    }

    /**
     * @param \Domain\Order\Models\OrderLine $orderLine
     * @param \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $medias
     * @param string $collection
     * @return void
     */
    private function copyMediaToOrderLine(OrderLine $orderLine, MediaCollection $medias, string $collection): void
    {
        foreach ($medias as $media) {
            $orderLine->addMediaFromUrl($media->getUrl())->toMediaCollection($collection);
        }
    }
}

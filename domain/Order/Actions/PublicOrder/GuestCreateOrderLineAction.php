<?php

declare(strict_types=1);

namespace Domain\Order\Actions\PublicOrder;

use Domain\Cart\Actions\PublicCart\GuestCartSummaryAction;
use Domain\Cart\DataTransferObjects\CartSummaryTaxData;
use Domain\Cart\DataTransferObjects\GuestCartSummaryShippingData;
use Domain\Order\DataTransferObjects\GuestPlaceOrderData;
use Domain\Order\DataTransferObjects\GuestPreparedOrderData;
use Domain\Order\Models\Order;
use Domain\Order\Models\OrderLine;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductVariant;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;

readonly class GuestCreateOrderLineAction
{
    public function __construct(
        private GuestCartSummaryAction $guestCartSummaryAction,
    ) {}

    public function execute(Order $order, GuestPlaceOrderData $placeOrderData, GuestPreparedOrderData $guestPreparedOrderData): void
    {
        foreach ($guestPreparedOrderData->cartLine as $cartLine) {

            $summary = $this->guestCartSummaryAction->execute(
                $cartLine,
                new CartSummaryTaxData(
                    $guestPreparedOrderData->countries->billingCountry->id,
                    $guestPreparedOrderData->states->billingState->id,
                ),
                new GuestCartSummaryShippingData(
                    $guestPreparedOrderData->shippingReceiverData,
                    $guestPreparedOrderData->shippingAddressData,
                    $guestPreparedOrderData->shippingMethod
                ),
                $guestPreparedOrderData->discount,
                $placeOrderData->serviceId
            );

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

            $total = $summary->initialSubTotal + $summary->taxTotal;

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
                'sub_total' => $summary->initialSubTotal,
                'discount_total' => 0,
                'total' => $total,
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
     * @param  \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media>  $medias
     */
    private function copyMediaToOrderLine(OrderLine $orderLine, MediaCollection $medias, string $collection): void
    {
        foreach ($medias as $media) {
            $orderLine->addMediaFromUrl($media->getUrl())->toMediaCollection($collection);
        }
    }
}

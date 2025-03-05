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
use Domain\Product\Models\ProductOptionValue;
use Domain\Product\Models\ProductVariant;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;

readonly class CreateOrderLineAction
{
    public function __construct(
        private CartSummaryAction $cartSummaryAction
    ) {
    }

    public function execute(Order $order, PlaceOrderData $placeOrderData, PreparedOrderData $preparedOrderData): void
    {
        foreach ($preparedOrderData->cartLine as $cartLine) {

            /** @var \Domain\Address\Models\State $state */
            $state = $preparedOrderData->billingAddress->state;

            /** @var \Domain\Address\Models\Country $country */
            $country = $state->country;

            $summary = $this->cartSummaryAction->execute(
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

            $initialSellingPrice = (float) $cartLine->purchasable->selling_price;

            $sellingPrice = $this->cartSummaryAction->getTierSellingPrice($cartLine->purchasable, $initialSellingPrice);

            $name = null;
            if ($cartLine->purchasable instanceof Product) {
                /** @var \Domain\Product\Models\Product $product */
                $product = $cartLine->purchasable;

                $name = $product->name;
            } elseif ($cartLine->purchasable instanceof ProductVariant) {
                /** @var \Domain\Product\Models\Product $product */
                $product = $cartLine->purchasable->product;

                $name = $product->name;

                $newCombination = $cartLine->purchasable->combination;

                foreach ($newCombination as &$combinationData) {
                    /** @var \Domain\Product\Models\ProductOptionValue $productOptionValue */
                    $productOptionValue = ProductOptionValue::with('media')
                        ->where('id', $combinationData['option_value_id'])->first();

                    $combinationData['option_value_data'] = $productOptionValue->data;
                }

                $cartLine->purchasable->combination = $newCombination;
            }

            $total = $summary->initialSubTotal + $summary->taxTotal;

            $orderLine = OrderLine::create([
                'uuid' => (string) Str::uuid(),
                'order_id' => $order->id,
                'purchasable_id' => $cartLine->purchasable_id,
                'purchasable_type' => $cartLine->purchasable_type,
                'purchasable_sku' => $cartLine->purchasable->sku,
                'name' => $name,
                'unit_price' => $sellingPrice,
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
                /** @var \Domain\Product\Models\ProductVariant $productVariant */
                $productVariant = $cartLine->purchasable;

                $productOptionMedia = collect();

                foreach ($productVariant->combination as $combinationData) {
                    /** @var \Domain\Product\Models\ProductOptionValue $productOptionValue */
                    $productOptionValue = ProductOptionValue::with('media')
                        ->where('id', $combinationData['option_value_id'])->first();

                    if ($productOptionValue->hasMedia('media')) {
                        $productOptionMedia = $productOptionMedia->merge($productOptionValue->media);
                    }
                }

                if ($productOptionMedia->isEmpty()) {
                    /** @var \Domain\Product\Models\Product $product */
                    $product = $productVariant->product;

                    $purchasableMedias = $product->getMedia('image');
                    $this->copyMediaToOrderLine($orderLine, $purchasableMedias, 'order_line_images');
                } else {
                    $productOptionMedias = MediaCollection::make($productOptionMedia);

                    $this->copyMediaToOrderLine($orderLine, $productOptionMedias, 'order_line_images');
                }
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

<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Domain\Order\DataTransferObjects\ProductOrderData;
use Domain\Order\DataTransferObjects\ProductVariantOrderData;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductOptionValue;
use Domain\Product\Models\ProductVariant;
use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Cart\Models\CartLine
 */
class CartLineResource extends JsonApiResource
{
    #[\Override]
    public function toAttributes(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'quantity' => $this->quantity,
            'remarks' => [
                'data' => $this->remarks,
                'media' => MediaResource::collection($this->media),
            ],
            'purchasable' => function () {
                if ($this->purchasable instanceof Product) {
                    return ProductOrderData::fromProduct($this->purchasable);
                } elseif ($this->purchasable instanceof ProductVariant) {
                    return ProductVariantOrderData::fromProductVariant($this->purchasable);
                }
            },
            'media' => function () {
                if ($this->purchasable instanceof Product) {
                    return MediaResource::collection($this->purchasable->media);
                } elseif ($this->purchasable instanceof ProductVariant) {
                    $productVariant = $this->purchasable;

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

                        return MediaResource::collection(collect($product->media));
                    } else {
                        return MediaResource::collection($productOptionMedia);
                    }
                }
            },
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Domain\Order\DataTransferObjects\ProductOrderData;
use Domain\Order\DataTransferObjects\ProductVariantOrderData;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductVariant;
use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Cart\Models\CartLine
 */
class CartLineResource extends JsonApiResource
{
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
                    return ProductOrderData::fromArray($this->purchasable->toArray());
                } elseif ($this->purchasable instanceof ProductVariant) {
                    return ProductVariantOrderData::fromArray($this->purchasable->toArray());
                }
            },
            'media' => function () {
                if ($this->purchasable instanceof Product) {
                    return MediaResource::collection($this->purchasable->media);
                } elseif ($this->purchasable instanceof ProductVariant) {
                    return MediaResource::collection(collect($this->purchasable->product->media));
                }
            },
        ];
    }
}

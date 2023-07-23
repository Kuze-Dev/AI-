<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Domain\Product\Models\Product;
use Domain\Product\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
            // 'cart_id' => $this->cart_id,
            'quantity' => $this->quantity,
            'remarks' => [
                'data' => $this->remarks,
                'media' => MediaResource::collection($this->media),
            ],
            'purchasable' => function () {
                if ($this->purchasable instanceof Product) {
                    return ProductResource::make($this->purchasable);
                } elseif ($this->purchasable instanceof ProductVariant) {
                    return ProductResource::make($this->purchasable->product);
                }
            },
            "media" => function () {
                if ($this->purchasable instanceof Product) {
                    return MediaResource::collection($this->purchasable->media);
                } elseif ($this->purchasable instanceof ProductVariant) {
                    return MediaResource::collection(collect($this->purchasable->product->media));
                }
            },
        ];
    }
}

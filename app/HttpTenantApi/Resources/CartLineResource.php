<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

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
            'id' => $this->id,
            'cart_id' => $this->cart_id,
            'quantity' => $this->quantity,
            'remarks' => [
                'data' => $this->remarks,
                'media' => MediaResource::collection($this->media),
            ],
            'purchasable' => function () {
                if ($this->purchasable instanceof Product) {
                    return ProductResource::make($this->purchasable);
                } elseif ($this->purchasable instanceof ProductVariant) {
                    return $this->purchasable;
                }
            },
            "purchasable_media" => MediaResource::collection($this->purchasable->media)
        ];
    }
}

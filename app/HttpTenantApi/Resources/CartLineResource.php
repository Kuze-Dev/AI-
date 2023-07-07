<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Domain\Product\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\QueryBuilder\QueryBuilder;
use TiMacDonald\JsonApi\JsonApiResource;

class CartLineResource extends JsonApiResource
{
    public function toAttributes(Request $request): array
    {
        return [
            'id' => $this->id,
            'cart_id' => $this->cart_id,
            'quantity' => $this->quantity,
            'meta' => $this->meta,
            'purchasable' => $this->purchasable->toArray(),
            'remarks_images' => $this->media->toArray()
        ];
    }

    public function toRelationships(Request $request): array
    {
        return [
            'purchasable' => function () {
                switch ($this->purchasable_type) {
                    case 'Domain\Product\Models\Product': {
                            return ProductResource::make($this->purchasable);
                        }
                    case 'Domain\Product\Models\ProductVariant': {
                            return ProductVariantResource::make($this->purchasable);
                        }
                }
            }
        ];
    }
}

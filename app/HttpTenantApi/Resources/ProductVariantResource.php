<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Product\Models\ProductVariant
 */
class ProductVariantResource extends JsonApiResource
{
    #[\Override]
    public function toAttributes(Request $request): array
    {
        return [
            'sku' => $this->sku,
            'combination' => $this->combination,
            'retail_price' => $this->retail_price,
            'selling_price' => $this->selling_price,
            'stock' => $this->stock,
            'status' => $this->status,
        ];
    }

    /** @return array<string, callable> */
    #[\Override]
    public function toRelationships(Request $request): array
    {
        return [
            'product' => fn () => ProductResource::make($this->product),
            'media' => fn () => MediaResource::collection($this->media),
        ];
    }
}

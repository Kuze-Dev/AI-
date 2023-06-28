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
    public function toAttributes(Request $request): array
    {
        return [
            'sku',
            'combination',
            'retail_price',
            'selling_price',
            'stock',
            'status',
        ];
    }

    /** @return array<string, callable> */
    public function toRelationships(Request $request): array
    {
        return [
            'product' => fn () => ProductResource::make($this->product),
        ];
    }
}

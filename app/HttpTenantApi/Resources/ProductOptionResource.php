<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Product\Models\ProductOption
 */
class ProductOptionResource extends JsonApiResource
{
    public function toAttributes(Request $request): array
    {
        return [
            'name',
            'slug',
        ];
    }

    /** @return array<string, callable> */
    public function toRelationships(Request $request): array
    {
        return [
            'product' => fn () => ProductResource::make($this->product),
            'productOptionValues' => fn () => ProductResource::make($this->productOptionValues),
        ];
    }
}

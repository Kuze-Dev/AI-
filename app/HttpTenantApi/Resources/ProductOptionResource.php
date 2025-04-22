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
    #[\Override]
    public function toAttributes(Request $request): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'is_custom' => $this->is_custom,
            'productOptionValues' => $this->productOptionValues,
        ];
    }

    /** @return array<string, callable> */
    #[\Override]
    public function toRelationships(Request $request): array
    {
        return [
            'product' => fn () => ProductResource::make($this->product),
            'productOptionValues' => fn () => ProductOptionValueResource::make($this->productOptionValues),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Product\Models\ProductVariant
 */
class OrderProductVariantResource extends JsonApiResource
{
    public function toAttributes(Request $request): array
    {
        return [
            'sku' => $this->sku,
            'combination' => $this->combination,
            'retail_price' => $this->retail_price,
            'selling_price' => $this->selling_price,
            'stock' => $this->stock,
            'status' => $this->status,
            'product' => ProductResource::make($this->product),
        ];
    }
}

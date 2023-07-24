<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Domain\Product\Models\Product;
use Domain\Product\Models\ProductVariant;
use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Order\Models\OrderLine
 */
class OrderLineResource extends JsonApiResource
{
    public function toAttributes(Request $request): array
    {
        return  [
            'purchasable_id' => $this->purchasable_id,
            'purchasable_sku' => $this->purchasable_sku,
            'name' => $this->name,
            'unit_price' => $this->unit_price,
            'quantity' => $this->quantity,
            'tax_total' => $this->tax_total,
            'sub_total' => $this->sub_total,
            'discount_total' => $this->discount_total,
            'total' => $this->total,
            // 'purchasable' => function () {
            //     if ($this->purchasable_data instanceof Product) {
            //         return ProductResource::make($this->purchasable_data);
            //     } elseif ($this->purchasable_data instanceof ProductVariant) {
            //         return ProductVariantResource::make($this->purchasable_data);
            //     }
            // },
            'purchasable' => $this->purchasable_data,
            'review' => $this->review ? ReviewResource::make($this->review) : null,
            'remarks' => [
                'data' => $this->remarks_data,
                'media' => MediaResource::collection($this->media->filter(
                    fn ($media) => $media->collection_name === 'order_line_notes'
                )),
            ],
            'media' => MediaResource::collection($this->media->filter(
                fn ($media) => $media->collection_name === 'order_line_images'
            )),
        ];
    }
}
